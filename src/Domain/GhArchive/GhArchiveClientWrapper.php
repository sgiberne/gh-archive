<?php

namespace App\Domain\GhArchive;

use Symfony\Component\Filesystem\Filesystem;

class GhArchiveClientWrapper
{
    private Filesystem $filesystem;
    private string $tmpDir;
    private GhArchiveConnection $ghArchiveConnection;

    public function __construct(string $tmpDir, Filesystem $filesystem, GhArchiveConnection $ghArchiveConnection)
    {
        $this->tmpDir = $tmpDir;
        $this->filesystem = $filesystem;
        $this->ghArchiveConnection = $ghArchiveConnection;
    }

    public function getGhArchiveConnection(): GhArchiveConnection
    {
        return $this->ghArchiveConnection;
    }

    public function setDateTime(\DateTime $dateTime): self
    {
        $hour = $dateTime->format('H');
        $dateTime->setTime($hour, 0, 0);
        $this->ghArchiveConnection->getGhArchiveConfiguration()->setDateTime($dateTime);

        return $this;
    }

    public function storeFile(): string
    {
        $newFile = $this->getStoredFilePath();
        $this->filesystem->dumpFile($newFile, file_get_contents($this->getFilepath()));

        return $newFile;
    }

    public function isStoredFileExists(): bool
    {
        $filePath = $this->getStoredFilePath();

        return $this->filesystem->exists($filePath) && is_readable($filePath);
    }

    public function getContent(): array
    {
        $filePath = $this->getStoredFilePath();

        return $this->isStoredFileExists() ? gzfile($filePath) : [];
    }

    public function getFilepath(): string
    {
        return $this->ghArchiveConnection->getFilePathForOneHour();
    }

    public function getStoredFilePath(): string
    {
        return $this->tmpDir.DIRECTORY_SEPARATOR.$this->ghArchiveConnection->getFilenameForOneHour();
    }

    public function removeStoredFile(): void
    {
        $this->filesystem->remove($this->getStoredFilePath());
    }
}
