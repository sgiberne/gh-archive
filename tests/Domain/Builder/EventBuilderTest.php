<?php

namespace Tests\Domain\Builder;

use App\Domain\Builder\ActorBuilder;
use App\Domain\Builder\EventBuilder;
use App\Domain\Builder\OrganisationBuilder;
use App\Domain\Builder\RepositoryBuilder;
use App\Domain\Entity\Actor;
use App\Domain\Entity\Event;
use App\Domain\Entity\Organisation;
use App\Domain\Entity\Repository;
use App\Domain\Factory\Data\ActorFactory;
use App\Domain\Factory\Data\OrganisationFactory;
use App\Domain\Factory\Data\RepositoryFactory;
use App\Domain\Repository\ActorRepository;
use App\Domain\Repository\OrganisationRepository;
use App\Domain\Repository\RepositoryRepository;
use PHPUnit\Framework\TestCase;

class EventBuilderTest extends TestCase
{
    /**
     * @dataProvider provideValidData
     */
    public function testBuild(
        array $data,
        ?Actor $ActorMocked,
        ?Repository $repositoryMocked,
        ?Organisation $organisationMocked,
        ?Event $expectedEvent
    ): void {
        $actorRepositoryMock = $this->createMock(ActorRepository::class);
        $actorRepositoryMock
            ->method('find')
            ->willReturn($ActorMocked);
        $actorBuilder = new ActorBuilder($actorRepositoryMock, new ActorFactory());

        $organisationRepositoryMock = $this->createMock(OrganisationRepository::class);
        $organisationRepositoryMock
            ->method('find')
            ->willReturn($organisationMocked);
        $organisationBuilder = new OrganisationBuilder($organisationRepositoryMock, new OrganisationFactory());

        $repositoryRepositoryMock = $this->createMock(RepositoryRepository::class);
        $repositoryRepositoryMock
            ->method('find')
            ->willReturn($repositoryMocked);
        $repositoryBuilder = new RepositoryBuilder($repositoryRepositoryMock, new RepositoryFactory());

        $repositoryRepositoryMock = $this->createMock(RepositoryRepository::class);
        $repositoryRepositoryMock
            ->method('find')
            ->willReturn($repositoryMocked);

        $eventBuilder = new EventBuilder(
            $actorBuilder,
            $organisationBuilder,
            $repositoryBuilder,
        );
        $event = $eventBuilder->build($data);

        if (null === $expectedEvent) {
            $this->assertNull($event);
        } else {
            $this->assertInstanceOf(Event::class, $event);
            $this->assertEquals($expectedEvent, $event);
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
            null,
            null,
        ];

        yield [
            [
                'id' => 1,
                'repo' => ['id' => 1],
                'actor' => ['id' => 1],
            ],
            null,
            null,
            null,
            (new Event())
                ->setId(1)
                ->setCreatedAt(null)
                ->setPublic(true)
                ->setType('')
                ->setPayload([])
                ->setActor(
                    (new Actor())
                        ->setId(1)
                        ->setUrl('')
                        ->setAvatarUrl('')
                        ->setGravatarId('')
                        ->setLogin('')
                        ->setDisplayLogin('')
                )
                ->setRepository(
                    (new Repository())
                        ->setId(1)
                        ->setUrl('')
                        ->setName('')
                )
                ->setOrganisation(null),
        ];

        yield [
            [
                'id' => 1,
                'public' => false,
                'created_at' => '2020-11-01 12:00:00',
                'type' => 'commit',
                'payload' => ['test' => 'test payload'],
                'repo' => ['id' => 1, 'url' => 'google.fr'],
                'actor' => ['id' => 1, 'login' => 'name'],
                'org' => ['id' => 2, 'avatar_url' => 'avatar.com/test.png'],
            ],
            null,
            null,
            null,
            (new Event())
                ->setId(1)
                ->setCreatedAt(new \DateTime('2020-11-01 12:00:00'))
                ->setPublic(false)
                ->setType('commit')
                ->setPayload(['test' => 'test payload'])
                ->setActor(
                    (new Actor())
                        ->setId(1)
                        ->setUrl('')
                        ->setAvatarUrl('')
                        ->setGravatarId('')
                        ->setLogin('name')
                        ->setDisplayLogin('')
                )
                ->setRepository(
                    (new Repository())
                        ->setId(1)
                        ->setUrl('google.fr')
                        ->setName(''),
                )
                ->setOrganisation(
                    (new Organisation())
                        ->setId(2)
                        ->setUrl('')
                        ->setAvatarUrl('avatar.com/test.png')
                        ->setGravatarId('')
                        ->setLogin(''),
                ),
        ];

        yield [
            [
                'id' => 1,
                'public' => false,
                'created_at' => '2021-01-01 08:00:00',
                'type' => 'commit',
                'payload' => ['test' => 'test payload'],
                'repo' => ['id' => 123, 'url' => 'google.fr'],
                'actor' => ['id' => 99, 'login' => 'name'],
                'org' => ['id' => 987, 'avatar_url' => 'avatar.com/test.png'],
            ],

            // Actor
            (new Actor())
                ->setId(99)
                ->setUrl('stephane.giberne.fr')
                ->setAvatarUrl('stephane.giberne.fr/avatar.jpg')
                ->setGravatarId('12345')
                ->setLogin('sgiberne')
                ->setDisplayLogin('sgiberne'),

            // Repository
            (new Repository())
                ->setId(123)
                ->setUrl('stephane.giberne.fr')
                ->setName('sgiberne'),

            //Organisation
            (new Organisation())
                ->setId(987)
                ->setUrl('google.fr')
                ->setAvatarUrl('google.fr/avatar.jpg')
                ->setGravatarId('56789')
                ->setLogin('anzio'),

            // Event
            (new Event())
                ->setId(1)
                ->setCreatedAt(new \DateTime('2021-01-01 08:00:00'))
                ->setPublic(false)
                ->setType('commit')
                ->setPayload(['test' => 'test payload'])
                ->setActor(
                    (new Actor())
                        ->setId(99)
                        ->setUrl('stephane.giberne.fr')
                        ->setAvatarUrl('stephane.giberne.fr/avatar.jpg')
                        ->setGravatarId('12345')
                        ->setLogin('sgiberne')
                        ->setDisplayLogin('sgiberne'),
                )
                ->setRepository(
                    (new Repository())
                        ->setId(123)
                        ->setUrl('stephane.giberne.fr')
                        ->setName('sgiberne'),
                )
                ->setOrganisation(
                    (new Organisation())
                        ->setId(987)
                        ->setUrl('google.fr')
                        ->setAvatarUrl('google.fr/avatar.jpg')
                        ->setGravatarId('56789')
                        ->setLogin('anzio'),
                ),
        ];
    }
}
