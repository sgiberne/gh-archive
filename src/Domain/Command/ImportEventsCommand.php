<?php

namespace App\Domain\Command;

use App\Domain\Builder\EventBuilder;
use App\Domain\DependencyInjection\GhArchiveConfiguration;
use App\Domain\DependencyInjection\GhArchiveConnection;
use App\Domain\DTO\Command\ImportEventsDTO;
use App\Domain\Entity\Event;
use App\Domain\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ImportEventsCommand extends Command
{
    protected static $defaultName = 'app:import:events';

    private const FLUSH_AT = 5000;

    private string $tmpDir;
    private EventRepository $eventRepository;
    private ValidatorInterface $validator;
    private EventBuilder $eventBuilder;
    private EntityManagerInterface $entityManager;
    private Filesystem $filesystem;
    private LoggerInterface $logger;

    public function __construct(
        string $tmpDir,
        EventRepository $eventRepository,
        ValidatorInterface $validator,
        EventBuilder $eventBuilder,
        EntityManagerInterface $entityManager,
        Filesystem $filesystem,
        LoggerInterface $importEventsLogger,
        string $name = null
    ) {
        $this->tmpDir = $tmpDir;
        $this->filesystem = $filesystem;
        $this->eventRepository = $eventRepository;
        $this->validator = $validator;
        $this->eventBuilder = $eventBuilder;
        $this->entityManager = $entityManager;
        $this->logger = $importEventsLogger;
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $defaultFormat = (new DateTime())->format;

        $this
            ->setDescription('Import GH Archive events from Big Query')
            ->addArgument('dateTime', InputArgument::OPTIONAL, "A DateTime from today to the past to extract. . Allowed format: $defaultFormat. Default: yesterday", (new \DateTime())->modify('yesterday')->format($defaultFormat))
            ->addOption('offset', null, InputOption::VALUE_OPTIONAL, 'Offset', 0)
            ->addOption('limit', null, InputOption::VALUE_OPTIONAL, 'Limit', 10000)
            ->addOption('clear', null, InputOption::VALUE_OPTIONAL, 'Clear tempory file', false);
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
        $nbPersisted = 0;

        $output->writeln("Extract for {$ghArchiveConfiguration->getDate()} from {$ghArchiveConfiguration->getHour()}:00:00 to {$ghArchiveConfiguration->getHour()}:59:59");
        $output->writeln("Offset {$importEventsDTO->offset}. Limit {$importEventsDTO->limit}");

        if (!$this->filesystem->exists($tmpFile)) {
            $output->writeln("Will download this file $filePath");
            $this->filesystem->dumpFile($tmpFile, file_get_contents($filePath));
        }

        $output->writeln("Will use this file $tmpFile");

        $eventsJson = array_slice(gzfile($tmpFile), $importEventsDTO->offset, $importEventsDTO->limit);

        $output->writeln(count($eventsJson).' events found');
        $progressBar = new ProgressBar($output, $importEventsDTO->limit);

        foreach ($eventsJson as $key => $eventJson) {
            try {
                $event = $this->buildEvent($eventJson);
            } catch (\RuntimeException $exception) {
                $output->writeln($exception->getMessage());
                continue;
            }

            $output->writeln("Built event {$event->getId()}");
            $this->entityManager->persist($event);
            ++$nbPersisted;

            if (0 === $nbPersisted % self::FLUSH_AT) {
                $output->writeln('Write in database');
                $this->entityManager->flush();
                $this->entityManager->clear();
            }

            unset($eventsJson[$key]);
            $progressBar->advance();
        }

        if (!empty($this->entityManager->getUnitOfWork()->getIdentityMap())) {
            $output->writeln('Write in database');
            $this->entityManager->flush();
        }

        if ($input->getArgument('clear')) {
            $this->filesystem->remove($tmpFile);
        }

        $output->writeln($nbPersisted.' written');
        $output->writeln('Done');
        $progressBar->finish();

        return Command::SUCCESS;
    }

    private function buildEvent(string $json): Event
    {
        try {
            $eventData = json_decode($json, true, 1024, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            $this->logger->warning('Invalid data', ['data' => $json]);
            throw new \RuntimeException('Invalide json data');
        }

        $event = $this->eventRepository->find($eventData['id']);
        if ($event instanceof Event) {
            throw new \RuntimeException("Event {$event->getId()} already exist");
        }

        $event = $this->eventBuilder->build($eventData);
        $constraintViolationList = $this->validator->validate($event);

        if ($constraintViolationList->count() > 0) {
            $this->logger->warning('Constraint violation', ['violations' => $constraintViolationList]);
            throw new \RuntimeException("Constraint violation: $constraintViolationList");
        }

        return $event;
    }
}
