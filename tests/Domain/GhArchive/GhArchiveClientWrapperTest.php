<?php

namespace App\Tests\Domain\GhArchive;

use App\Domain\GhArchive\GhArchiveClientWrapper;
use App\Domain\GhArchive\GhArchiveConfiguration;
use App\Domain\GhArchive\GhArchiveConnection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class GhArchiveClientWrapperTest extends TestCase
{
    private const TMP_DIR = 'tests/data/tmp';

    private ?GhArchiveClientWrapper $ghArchiveClientWrapper;

    public function setUp(): void
    {
        $this->ghArchiveClientWrapper = self::createGhArchiveClientWrapper();
        parent::setUp();
    }

    public function tearDown(): void
    {
        $this->ghArchiveClientWrapper = null;
        parent::tearDown();
    }

    public static function setUpAfterClass(): void
    {
        self::createGhArchiveClientWrapper()->removeStoredFile();
    }

    public static function tearDownAfterClass(): void
    {
        self::createGhArchiveClientWrapper()->removeStoredFile();
    }

    private static function createGhArchiveClientWrapper(): GhArchiveClientWrapper
    {
        $fileSystem = new Filesystem();
        $ghArchiveConfiguration = new GhArchiveConfiguration('tests/data');
        $ghArchiveConnection = new GhArchiveConnection($ghArchiveConfiguration);

        $ghArchiveClientWrapper = new GhArchiveClientWrapper(self::TMP_DIR, $fileSystem, $ghArchiveConnection);
        $ghArchiveClientWrapper->setDateTime(new \DateTime('2020-10-01 22:00:00'));

        return $ghArchiveClientWrapper;
    }

    public function testStoreFileDoesNotExist(): void
    {
        $this->assertFalse($this->ghArchiveClientWrapper->isStoredFileExists());
    }

    /**
     * @depends testStoreFileDoesNotExist
     */
    public function testStoreFile(): void
    {
        $this->assertSame($this->ghArchiveClientWrapper->getStoredFilePath(), $this->ghArchiveClientWrapper->storeFile());
    }

    /**
     * @depends testStoreFile
     */
    public function testStoreFileExist(): void
    {
        $this->assertTrue($this->ghArchiveClientWrapper->isStoredFileExists());
    }

    /**
     * @depends testStoreFileExist
     */
    public function testStoredFileDeleted(): void
    {
        $this->ghArchiveClientWrapper->removeStoredFile();
        
        $this->assertFalse($this->ghArchiveClientWrapper->isStoredFileExists());
    }
}
