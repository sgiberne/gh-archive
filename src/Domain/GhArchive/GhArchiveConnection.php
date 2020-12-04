<?php

namespace App\Domain\GhArchive;

class GhArchiveConnection
{
    private GhArchiveConfiguration $ghArchiveConfiguration;

    private string $tmpDir = '/var/tmp/ghArchive';

    public function __construct(GhArchiveConfiguration $ghArchiveConfiguration)
    {
        $this->ghArchiveConfiguration = $ghArchiveConfiguration;
    }

    public function getGhArchiveConfiguration(): GhArchiveConfiguration
    {
        return $this->ghArchiveConfiguration;
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
            '%s/%s',
            $this->ghArchiveConfiguration->getHost(),
            $this->getFilenameForOneHour(),
        );
    }
}
