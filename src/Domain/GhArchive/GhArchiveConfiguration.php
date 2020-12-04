<?php

namespace App\Domain\GhArchive;

class GhArchiveConfiguration
{
    private const FILE_TYPE = '.json.gz';

    private string $ghArchiveHost;
    private \DateTime $dateTime;

    public function __construct(string $ghArchiveHost)
    {
        $this->ghArchiveHost = $ghArchiveHost;
    }

    public function getHost(): string
    {
        return $this->ghArchiveHost;
    }

    public function getFileType(): string
    {
        return self::FILE_TYPE;
    }

    public function getDateTime(): string
    {
        return $this->dateTime->format('Y-m-d H:m:i');
    }

    public function setDateTime(\DateTime $dateTime): self
    {
        $this->dateTime = $dateTime;

        return $this;
    }

    public function getDate(): ?string
    {
        return $this->dateTime instanceof \DateTime ? $this->dateTime->format('Y-m-d') : null;
    }

    public function getHour(): ?int
    {
        return $this->dateTime instanceof \DateTime ? $this->dateTime->format('H') : null;
    }
}
