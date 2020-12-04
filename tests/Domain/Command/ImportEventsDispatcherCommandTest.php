<?php

namespace App\Tests\Domain\Command;

use App\Domain\GhArchive\GhArchiveClientWrapper;
use App\Tests\GhArchiveProviderTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Messenger\Transport\InMemoryTransport;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Symfony\Contracts\Service\ResetInterface;

class ImportEventsDispatcherCommandTest extends KernelTestCase
{
    use GhArchiveProviderTrait;

    private ?Application $application;
    private ?InMemoryTransport $transport;
    private ?GhArchiveClientWrapper $ghArchiveClientWrapper;

    /**
     * @dataProvider provideData
     */
    public function testExecute(int $offset, int $limit): void
    {
        $this->ghArchiveClientWrapper->storeFile();
        $nbMessagesExpected = ceil(($this->ghArchiveClientWrapper->count() - $offset) / $limit);
        $this->ghArchiveClientWrapper->removeStoredFile();

        $command = $this->application->find('app:import:events-dispatcher');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'dateTime' => self::$dateTimeToTest,
                '--offset' => $offset,
                '--limit' => $limit,
            ]
        );

        $this->assertStringContainsString('Done', $commandTester->getDisplay());
        $this->assertTrue($this->ghArchiveClientWrapper->isStoredFileExists());

        $this->assertCount($nbMessagesExpected, $this->transport->get());
    }

    /**
     * @dataProvider provideInvalidData
     */
    public function testExecuteFail(string $dateTime, string $offset, string $limit, string $expectedException, string $expectedMessage): void
    {
        $command = $this->application->find('app:import:events-dispatcher');
        $commandTester = new CommandTester($command);

        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedMessage);

        $commandTester->execute(
            [
                'dateTime' => $dateTime,
                '--offset' => $offset,
                '--limit' => $limit,
            ]
        );

        $this->assertStringNotContainsString('Done', $commandTester->getDisplay());
    }

    public function setUp(): void
    {
        $kernel = static::bootKernel();
        $this->application = new Application($kernel);

        $this->transport = self::$container->has('messenger.transport.async') ? self::$container->get('messenger.transport.async') : null;

        if (!$this->transport instanceof TransportInterface || !$this->transport instanceof ResetInterface) {
            throw new \RuntimeException('Unexpected transport. Expected instance of '.TransportInterface::class.' and '.ResetInterface::class);
        }

        $this->transport->reset();

        $this->ghArchiveClientWrapper = self::createGhArchiveClientWrapper();

        parent::setUp();
    }

    public function tearDown(): void
    {
        $this->application = null;
        $this->transport = null;
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

    public function provideData(): iterable
    {
        yield [
            0,
            1000,
        ];

        yield [
            0,
            10000,
        ];

        yield [
            10000,
            10000,
        ];
    }

    public function provideInvalidData(): iterable
    {
        yield [
            '2020-10-02 12:00:00',
            0,
            1000,
            \RuntimeException::class,
            'file does not exist',
        ];

        yield [
            '2019-01-29 22:00:00',
            0,
            1000,
            \RuntimeException::class,
            'file does not exist',
        ];

        yield [
            '2019-01-29 22:00:00',
            'offset',
            1000,
            \RuntimeException::class,
            'Invalid type given',
        ];

        yield [
            '2019-01-29 22:00:00',
            12,
            'limit',
            \RuntimeException::class,
            'Invalid type given',
        ];
    }
}
