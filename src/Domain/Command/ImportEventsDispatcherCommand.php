<?php

namespace App\Domain\Command;

use App\Domain\DTO\Command\ImportEventsDTO;
use App\Domain\GhArchive\GhArchiveClientWrapper;
use App\Domain\Message\ImportEventMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ImportEventsDispatcherCommand extends Command
{
    protected static $defaultName = 'app:import:events-dispatcher';

    private GhArchiveClientWrapper $ghArchiveClientWrapper;
    private ValidatorInterface $validator;
    private MessageBusInterface $messageBus;
    private LoggerInterface $logger;

    public function __construct(
        GhArchiveClientWrapper $ghArchiveClientWrapper,
        MessageBusInterface $messageBus,
        ValidatorInterface $validator,
        LoggerInterface $importEventsLogger,
        string $name = null
    ) {
        $this->ghArchiveClientWrapper = $ghArchiveClientWrapper;
        $this->messageBus = $messageBus;
        $this->validator = $validator;
        $this->logger = $importEventsLogger;
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $defaultFormat = (new DateTime())->format;

        $this
            ->setDescription('Import GH Archive events from Big Query')
            ->addArgument('dateTime', InputArgument::OPTIONAL, "A DateTime from today to the past to extract. Allowed format: $defaultFormat. Default: yesterday", (new \DateTime())->modify('yesterday')->format($defaultFormat))
            ->addOption('offset', 'o', InputOption::VALUE_OPTIONAL, 'Offset', 0)
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Limit', 20000);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dateTime = $input->getArgument('dateTime');
        $offset = $input->getOption('offset');
        $limit = $input->getOption('limit');

        if (!is_string($dateTime) || !is_numeric($offset) || !is_numeric($limit)) {
            throw new \RuntimeException('Invalid type given');
        }

        $importEventsDTO = new ImportEventsDTO($dateTime, (int) $offset, (int) $limit);

        $constraintViolationList = $this->validator->validate($importEventsDTO);

        if ($constraintViolationList->count() > 0) {
            $this->logger->warning('Constraint violation', ['violations' => $constraintViolationList]);
            throw new \RuntimeException('Constraint violation');
        }

        $dateTime = \DateTime::createFromFormat((new DateTime())->format, $importEventsDTO->dateTime);

        if (false === $dateTime) {
            throw new \RuntimeException('Invalid dateTime');
        }

        $this->ghArchiveClientWrapper->setDateTime($dateTime);

        $filePath = $this->ghArchiveClientWrapper->getGhArchiveConnection()->getFilePathForOneHour();
        $dateToExtract = $this->ghArchiveClientWrapper->getGhArchiveConnection()->getGhArchiveConfiguration()->getDate();
        $hourToExtract = $this->ghArchiveClientWrapper->getGhArchiveConnection()->getGhArchiveConfiguration()->getHour();

        $output->writeln("Extract for $dateToExtract from $hourToExtract:00:00 to $hourToExtract:59:59");
        $output->writeln("Will download this file $filePath");

        $tmpFile = $this->ghArchiveClientWrapper->storeFile();
        $output->writeln("Will use this file $tmpFile");

        $eventsJson = $this->ghArchiveClientWrapper->getContent();
        $nbEvents = count($eventsJson);

        $output->writeln("$nbEvents events found");
        $progressBar = new ProgressBar($output, $nbEvents);
        $progressBar->advance($importEventsDTO->offset);

        for ($i = $importEventsDTO->offset; $i <= $nbEvents; $i += $importEventsDTO->limit) {
            $importEventMessage = new ImportEventMessage(
                $this->ghArchiveClientWrapper->getGhArchiveConnection()->getGhArchiveConfiguration()->getDateTime(),
                $i,
                $importEventsDTO->limit,
                ($nbEvents + $importEventsDTO->limit) > $nbEvents
            );

            $this->messageBus->dispatch($importEventMessage);
            $output->writeln("Message dispatched. Offset $i. Limit ".$importEventsDTO->limit);
        }

        $output->writeln('Done');

        return Command::SUCCESS;
    }
}
