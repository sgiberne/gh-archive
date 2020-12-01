<?php

namespace App\Domain\MessageHandler;

use App\Domain\Message\ImportEventMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Process\Process;

class ImportEventMessageHandler implements MessageHandlerInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function __invoke(ImportEventMessage $importEventMessage): void
    {
        $process = new Process(
            [
                'php',
                'bin/console',
                'app:import:events',
                $importEventMessage->dateTime,
                '--offset',
                $importEventMessage->offset,
                '--limit',
                $importEventMessage->limit,
                '--clear',
                $importEventMessage->lastOne,
            ],
        );
        $process->setTimeout(120);

        $this->logger->debug("Run command {$process->getCommandLine()}");

        $process->run();
    }
}
