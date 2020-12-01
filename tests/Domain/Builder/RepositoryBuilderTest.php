<?php

namespace Tests\Domain\Builder;

use App\Domain\Builder\RepositoryBuilder;
use App\Domain\Entity\Repository;
use App\Domain\Factory\Data\RepositoryFactory;
use App\Domain\Repository\RepositoryRepository;
use PHPUnit\Framework\TestCase;

class RepositoryBuilderTest extends TestCase
{
    /**
     * @dataProvider provideValidData
     */
    public function testBuild(array $data, ?Repository $repositoryMocked, ?Repository $expectedRepository): void
    {
        $repositoryRepositoryMock = $this->createMock(RepositoryRepository::class);
        $repositoryRepositoryMock
            ->method('find')
            ->willReturn($repositoryMocked);

        $repositoryBuilder = new RepositoryBuilder($repositoryRepositoryMock, new RepositoryFactory());
        $repository = $repositoryBuilder->build($data);

        if (null === $expectedRepository) {
            $this->assertNull($repository);
        } else {
            $this->assertInstanceOf(Repository::class, $repository);
            $this->assertEquals($expectedRepository, $repository);
        }
    }

    public function provideValidData(): iterable
    {
        yield [
            [
                'url' => 'stephane.giberne.fr',
            ],
            null,
            null,
        ];

        yield [
            [
                'id' => 1,
                'url' => 'stephane.giberne.fr',
                'name' => 'sgiberne',
            ],
            null,
            (new Repository())
                ->setId(1)
                ->setUrl('stephane.giberne.fr')
                ->setName('sgiberne'),
        ];

        yield [
            [
                'id' => 2,
            ],
            (new Repository())
                ->setId(2)
                ->setUrl('stephane.giberne.fr')
                ->setName('sgiberne'),
            (new Repository())
                ->setId(2)
                ->setUrl('stephane.giberne.fr')
                ->setName('sgiberne'),
        ];
    }
}
