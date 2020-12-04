<?php

namespace App\Tests\Domain\Factory\Data;

use App\Domain\Entity\Actor;
use App\Domain\Factory\Data\ActorFactory;
use PHPUnit\Framework\TestCase;

class ActorFactoryTest extends TestCase
{
    /**
     * @dataProvider provideValidData
     */
    public function testCreate(array $data, Actor $expectedActor): void
    {
        $actorFactory = new ActorFactory();
        $actor = $actorFactory->createFromArray($data);

        $this->assertEquals($actor, $expectedActor);
    }

    /**
     * @dataProvider provideInvalidData
     */
    public function testCreateWithInvalidData(array $data): void
    {
        $actorFactory = new ActorFactory();

        $this->expectException(\BadMethodCallException::class);

        $actorFactory->createFromArray($data);
    }

    public function provideValidData(): iterable
    {
        yield [
            [
                'id' => 1,
            ],
            (new Actor())
                ->setId(1)
                ->setUrl('')
                ->setAvatarUrl('')
                ->setGravatarId('')
                ->setLogin('')
                ->setDisplayLogin(''),
        ];

        yield [
            [
                'id' => 1,
                'url' => 'stephane.giberne.fr',
                'avatar_url' => 'stephane.giberne.fr/avatar.jpg',
                'gravatar_id' => 12345,
                'login' => 'sgiberne',
                'display_login' => 'sgiberne',
            ],
            (new Actor())
                ->setId(1)
                ->setUrl('stephane.giberne.fr')
                ->setAvatarUrl('stephane.giberne.fr/avatar.jpg')
                ->setGravatarId('12345')
                ->setLogin('sgiberne')
                ->setDisplayLogin('sgiberne'),
        ];
    }

    public function provideInvalidData(): iterable
    {
        yield [
            [
                'url' => 'stephane.giberne.fr',
                'avatar_url' => 'stephane.giberne.fr/avatar.jpg',
                'gravatar_id' => 12345,
                'login' => 'sgiberne',
                'display_login' => 'sgiberne',
            ],
        ];
    }
}
