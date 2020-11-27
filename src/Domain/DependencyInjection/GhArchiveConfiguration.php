<?php

namespace App\Domain\DependencyInjection;

class GhArchiveConfiguration
{
    private const HOST = 'https://data.gharchive.org';
    private const FILE_TYPE = '.json.gz';

    private \DateTime $dateTime;

    public function __construct(\DateTime $dateTime)
    {
        $this->dateTime = $dateTime;
    }

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

    public function getDate(): string
    {
        return $this->dateTime->format('Y-m-d');
    }

    public function getHour(): string
    {
        return $this->dateTime->format('H');
    }
}
