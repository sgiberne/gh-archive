<?php

namespace Tests\Domain\builder;

use App\Domain\Builder\ActorBuilder;
use App\Domain\Entity\Actor;
use App\Domain\Factory\Data\ActorFactory;
use App\Domain\Repository\ActorRepository;
use PHPUnit\Framework\TestCase;

class ActorBuilderTest extends TestCase
{
    /**
     * @dataProvider provideValidData
     */
    public function testBuild(array $data, ?Actor $ActorMocked, ?Actor $expectedActor): void
    {
        $actorRepositoryMock = $this->createMock(ActorRepository::class);
        $actorRepositoryMock
            ->method('find')
            ->willReturn($ActorMocked);

        $actorBuilder = new ActorBuilder($actorRepositoryMock, new ActorFactory());
        $actor = $actorBuilder->build($data);

        if ($expectedActor === null) {
            $this->assertNull($actor);
        } else {
            $this->assertInstanceOf(Actor::class, $actor);
            $this->assertEquals($expectedActor, $actor);
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
                'avatar_url' => 'stephane.giberne.fr/avatar.jpg',
                'gravatar_id' => 12345,
                'login' => 'sgiberne',
                'display_login' => 'sgiberne',
            ],
            null,
            (new Actor())
                ->setId(1)
                ->setUrl('stephane.giberne.fr')
                ->setAvatarUrl('stephane.giberne.fr/avatar.jpg')
                ->setGravatarId('12345')
                ->setLogin('sgiberne')
                ->setDisplayLogin('sgiberne'),
        ];

        yield [
            [
                'id' => 2,
            ],
            (new Actor())
                ->setId(2)
                ->setUrl('stephane.giberne.fr')
                ->setAvatarUrl('stephane.giberne.fr/avatar.jpg')
                ->setGravatarId('12345')
                ->setLogin('sgiberne')
                ->setDisplayLogin('sgiberne'),
            (new Actor())
                ->setId(2)
                ->setUrl('stephane.giberne.fr')
                ->setAvatarUrl('stephane.giberne.fr/avatar.jpg')
                ->setGravatarId('12345')
                ->setLogin('sgiberne')
                ->setDisplayLogin('sgiberne'),
        ];
    }
}
