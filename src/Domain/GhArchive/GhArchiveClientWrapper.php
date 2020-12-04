<?php

namespace App\Domain\GhArchive;

use Symfony\Component\Filesystem\Filesystem;

class GhArchiveClientWrapper
{
    private Filesystem $filesystem;
    private string $tmpDir;
    private GhArchiveConnection $ghArchiveConnection;

    /** @var array<int, array> */
    private array $content = [];

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
        $hour = (int)$dateTime->format('H');
        $dateTime->setTime($hour, 0, 0);
        $this->ghArchiveConnection->getGhArchiveConfiguration()->setDateTime($dateTime);

        return $this;
    }

    public function storeFile(): string
    {
        $newFile = $this->getStoredFilePath();

        if (!$this->filesystem->exists($this->getFilepath())) {
            throw new \RuntimeException($this->getFilepath().' file does not exist');
        }

        $content = file_get_contents($this->getFilepath());

        if (!is_string($content)) {
            throw new \RuntimeException($this->getFilepath().' has an empty content');
        }

        $this->filesystem->dumpFile($newFile, $content);

        return $newFile;
    }

    public function isStoredFileExists(): bool
    {
        $filePath = $this->getStoredFilePath();

        return $this->filesystem->exists($filePath) && is_readable($filePath);
    }

    /**
     * @return array<int, array>
     */
    public function getContent(): array
    {
        if (!empty($this->content)) {
            return $this->content;
        }

        $filePath = $this->getStoredFilePath();

        return $this->isStoredFileExists() ? $this->content = gzfile($filePath) : [];
    }

    public function count(): int
    {
        $this->getContent();

        return count($this->content);
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
