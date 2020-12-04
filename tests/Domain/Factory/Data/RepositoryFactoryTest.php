<?php

namespace App\Tests\Domain\Factory\Data;

use App\Domain\Entity\Repository;
use App\Domain\Factory\Data\RepositoryFactory;
use PHPUnit\Framework\TestCase;

class RepositoryFactoryTest extends TestCase
{
    /**
     * @dataProvider provideValidData
     */
    public function testCreate(array $data, Repository $expectedRepository): void
    {
        $repositoryFactory = new RepositoryFactory();
        $repository = $repositoryFactory->createFromArray($data);

        $this->assertEquals($repository, $expectedRepository);
    }

    /**
     * @dataProvider provideInvalidData
     */
    public function testCreateWithInvalidData(array $data): void
    {
        $repositoryFactory = new RepositoryFactory();

        $this->expectException(\BadMethodCallException::class);

        $repositoryFactory->createFromArray($data);
    }

    public function provideValidData(): iterable
    {
        yield [
            [
                'id' => 1,
            ],
            (new Repository())
                ->setId(1)
                ->setUrl('')
                ->setName(''),
        ];

        yield [
            [
                'id' => 1,
                'url' => 'stephane.giberne.fr',
                'name' => 'sgiberne',
            ],
            (new Repository())
                ->setId(1)
                ->setUrl('stephane.giberne.fr')
                ->setName('sgiberne'),
        ];
    }

    public function provideInvalidData(): iterable
    {
        yield [
            [
                'url' => 'stephane.giberne.fr',
                'name' => 'sgiberne',
            ],
        ];
    }
}
