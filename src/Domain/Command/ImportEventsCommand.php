<?php

namespace App\Domain\Command;

use App\Domain\Builder\EventBuilder;
use App\Domain\GhArchive\GhArchiveClientWrapper;
use App\Domain\GhArchive\GhArchiveConfiguration;
use App\Domain\GhArchive\GhArchiveConnection;
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
    private GhArchiveClientWrapper $ghArchiveClientWrapper;
    private EventRepository $eventRepository;
    private ValidatorInterface $validator;
    private EventBuilder $eventBuilder;
    private EntityManagerInterface $entityManager;
    private Filesystem $filesystem;
    private LoggerInterface $logger;

    public function __construct(
        string $tmpDir,
        GhArchiveClientWrapper $ghArchiveClientWrapper,
        EventRepository $eventRepository,
        ValidatorInterface $validator,
        EventBuilder $eventBuilder,
        EntityManagerInterface $entityManager,
        Filesystem $filesystem,
        LoggerInterface $importEventsLogger,
        string $name = null
    ) {
        $this->tmpDir = $tmpDir;
        $this->ghArchiveClientWrapper = $ghArchiveClientWrapper;
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
            ->addOption('clear', null, InputOption::VALUE_OPTIONAL, 'Clear temporary file', false);
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

        $this->ghArchiveClientWrapper->setDateTime($dateTime);

        $filePath = $this->ghArchiveClientWrapper->getGhArchiveConnection()->getFilePathForOneHour();
        $dateToExtract = $this->ghArchiveClientWrapper->getGhArchiveConnection()->getGhArchiveConfiguration()->getDate();
        $hourToExtract = $this->ghArchiveClientWrapper->getGhArchiveConnection()->getGhArchiveConfiguration()->getHour();

        $nbPersisted = 0;

        $output->writeln("Extract for $dateToExtract from $hourToExtract:00:00 to $hourToExtract:59:59");
        $output->writeln("Offset {$importEventsDTO->offset}. Limit {$importEventsDTO->limit}");

        if (!$this->ghArchiveClientWrapper->isStoredFileExists()) {
            $output->writeln("Will download this file $filePath");
            $this->ghArchiveClientWrapper->getStoredFilePath();
        }

        $tmpFile = $this->ghArchiveClientWrapper->storeFile();
        $output->writeln("Will use this file $tmpFile");

        $eventsJson = array_slice($this->ghArchiveClientWrapper->getContent(), $importEventsDTO->offset, $importEventsDTO->limit);

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

        if ($input->getOption('clear')) {
            $this->ghArchiveClientWrapper->removeStoredFile();
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
            dump($constraintViolationList);
            dump($event->getCreatedAt()); die;

            $this->logger->warning('Constraint violation', ['violations' => $constraintViolationList]);
            throw new \RuntimeException("Constraint violation: $constraintViolationList");
        }

        return $event;
    }
}
