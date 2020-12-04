<?php

namespace App\Tests\Domain\Builder;

use App\Domain\Builder\OrganisationBuilder;
use App\Domain\Entity\Organisation;
use App\Domain\Factory\Data\OrganisationFactory;
use App\Domain\Repository\OrganisationRepository;
use PHPUnit\Framework\TestCase;

class OrganisationBuilderTest extends TestCase
{
    /**
     * @dataProvider provideValidData
     */
    public function testBuild(array $data, ?Organisation $organisationMocked, ?Organisation $expectedOrganisation): void
    {
        $organisationRepositoryMock = $this->createMock(OrganisationRepository::class);
        $organisationRepositoryMock
            ->method('find')
            ->willReturn($organisationMocked);

        $organisationBuilder = new OrganisationBuilder($organisationRepositoryMock, new OrganisationFactory());
        $organisation = $organisationBuilder->build($data);

        if (null === $expectedOrganisation) {
            $this->assertNull($organisation);
        } else {
            $this->assertInstanceOf(Organisation::class, $organisation);
            $this->assertEquals($expectedOrganisation, $organisation);
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
            ],
            null,
            (new Organisation())
                ->setId(1)
                ->setUrl('stephane.giberne.fr')
                ->setAvatarUrl('stephane.giberne.fr/avatar.jpg')
                ->setGravatarId('12345')
                ->setLogin('sgiberne'),
        ];

        yield [
            [
                'id' => 2,
            ],
            (new Organisation())
                ->setId(2)
                ->setUrl('google.fr')
                ->setAvatarUrl('google.fr/avatar.jpg')
                ->setGravatarId('56789')
                ->setLogin('anzio'),
            (new Organisation())
                ->setId(2)
                ->setUrl('google.fr')
                ->setAvatarUrl('google.fr/avatar.jpg')
                ->setGravatarId('56789')
                ->setLogin('anzio'),
        ];
    }
}
