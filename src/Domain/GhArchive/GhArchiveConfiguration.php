<?php

namespace App\Domain\GhArchive;

class GhArchiveConfiguration
{
    private const HOST = 'https://data.gharchive.org';
    private const FILE_TYPE = '.json.gz';

    private \DateTime $dateTime;

    public function getHost(): string
    {
        return self::HOST;
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
