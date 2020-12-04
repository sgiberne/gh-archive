<?php

namespace App\Tests\Domain\GhArchive;

use App\Domain\GhArchive\GhArchiveClientWrapper;
use App\Tests\GhArchiveProviderTrait;
use PHPUnit\Framework\TestCase;

class GhArchiveClientWrapperTest extends TestCase
{
    use GhArchiveProviderTrait;

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
