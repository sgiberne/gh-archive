<?php

namespace App\Tests;

use App\Domain\GhArchive\GhArchiveClientWrapper;
use App\Domain\GhArchive\GhArchiveConfiguration;
use App\Domain\GhArchive\GhArchiveConnection;
use Symfony\Component\Filesystem\Filesystem;

trait GhArchiveProviderTrait
{
    protected static string $tmpDir = 'tests/data/tmp';
    protected static string $ghArchiveHost = 'tests/data';
    protected static string $dateTimeToTest = '2020-10-01 22:00:00';

    protected static function createGhArchiveClientWrapper(): GhArchiveClientWrapper
    {
        $fileSystem = new Filesystem();
        $ghArchiveConfiguration = new GhArchiveConfiguration(self::$ghArchiveHost);
        $ghArchiveConnection = new GhArchiveConnection($ghArchiveConfiguration);

        $ghArchiveClientWrapper = new GhArchiveClientWrapper(self::$tmpDir, $fileSystem, $ghArchiveConnection);
        $ghArchiveClientWrapper->setDateTime(new \DateTime(self::$dateTimeToTest));

        return $ghArchiveClientWrapper;
    }
}
