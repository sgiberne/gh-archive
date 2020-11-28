<?php

namespace App\Domain\Command;

use App\Domain\DependencyInjection\GhArchiveConfiguration;
use App\Domain\DependencyInjection\GhArchiveConnection;
use App\Domain\DTO\Command\ImportEventsDTO;
use App\Domain\Message\ImportEventMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ImportEventsDispatcherCommand extends Command
{
    protected static $defaultName = 'app:import:events-dispatcher';

    private string $tmpDir;
    private ValidatorInterface $validator;
    private MessageBusInterface $messageBus;
    private Filesystem $filesystem;
    private LoggerInterface $logger;

    public function __construct(
        string $tmpDir,
        MessageBusInterface $messageBus,
        ValidatorInterface $validator,
        Filesystem $filesystem,
        LoggerInterface $importEventsLogger,
        string $name = null
    ) {
        $this->tmpDir = $tmpDir;
        $this->filesystem = $filesystem;
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
        $importEventsDTO = new ImportEventsDTO($input->getArgument('dateTime'), $input->getOption('offset'), $input->getOption('limit'));
        $constraintViolationList = $this->validator->validate($importEventsDTO);

        if ($constraintViolationList->count() > 0) {
            $this->logger->warning('Constraint violation', ['violations' => $constraintViolationList]);
            throw new \RuntimeException("Constraint violation: $constraintViolationList");
        }

        $dateTime = \DateTime::createFromFormat((new DateTime())->format, $importEventsDTO->dateTime);
        $ghArchiveConfiguration = new GhArchiveConfiguration($dateTime);
        $ghArchiveConnection = new GhArchiveConnection($ghArchiveConfiguration);

        $filePath = $ghArchiveConnection->getFilePathForOneHour();
        $tmpFile = $this->tmpDir.$ghArchiveConnection->getFilenameForOneHour();

        $output->writeln("Extract for {$ghArchiveConfiguration->getDate()} from {$ghArchiveConfiguration->getHour()}:00:00 to {$ghArchiveConfiguration->getHour()}:59:59");
        $output->writeln("Will download this file $filePath");
        $output->writeln("Will use this file $tmpFile");

        $this->filesystem->dumpFile($tmpFile, file_get_contents($filePath));

        $eventsJson = gzfile($tmpFile);
        $nbEvents = count($eventsJson);

        $output->writeln("$nbEvents events found");
        $progressBar = new ProgressBar($output, $nbEvents);
        $progressBar->advance($importEventsDTO->offset);

        for ($i = $importEventsDTO->offset; $i <= $nbEvents; $i += $importEventsDTO->limit) {
            $importEventMessage = new ImportEventMessage(
                $ghArchiveConfiguration->getDateTime(),
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
