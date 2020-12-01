<?php

namespace App\Domain\DependencyInjection;

class GhArchiveConnection
{
    private GhArchiveConfiguration $ghArchiveConfiguration;

    private string $tmpDir = '/var/tmp/ghArchive';

    public function __construct(GhArchiveConfiguration $ghArchiveConfiguration)
    {
        $this->ghArchiveConfiguration = $ghArchiveConfiguration;
    }

    public function getFilenameForOneHour(): string
    {
        return sprintf(
            '%s-%s%s',
            $this->ghArchiveConfiguration->getDate(),
            $this->ghArchiveConfiguration->getHour(),
            $this->ghArchiveConfiguration->getFileType(),
        );
    }

    public function getFilePathForOneHour(): string
    {
        return sprintf(
            '%s/%s-%s%s',
            $this->ghArchiveConfiguration->getHost(),
            $this->ghArchiveConfiguration->getDate(),
            $this->ghArchiveConfiguration->getHour(),
            $this->ghArchiveConfiguration->getFileType(),
        );
    }

    public function getFilePath(): string
    {
        return $this->tmpDir.$this->getFilenameForOneHour();
    }

    public function importFile(): int
    {
        if (!is_dir($this->tmpDir) || !is_writable($this->tmpDir)) {
            throw new \RuntimeException("{$this->tmpDir} is not a dir or is not writable");
        }

        $filePath = $this->getFilePath();
        $bytes = file_put_contents($filePath, fopen($this->getFilenameForOneHour(), 'r'));

        return false === $bytes ? 0 : $bytes;
    }
}
