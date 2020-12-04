<?php

namespace App\Tests\Domain\Factory\Data;

use App\Domain\Entity\Organisation;
use App\Domain\Factory\Data\OrganisationFactory;
use PHPUnit\Framework\TestCase;

class OrganisationFactoryTest extends TestCase
{
    /**
     * @dataProvider provideValidData
     */
    public function testCreate(array $data, Organisation $expectedOrganisation): void
    {
        $organisationFactory = new OrganisationFactory();
        $organisation = $organisationFactory->createFromArray($data);

        $this->assertEquals($organisation, $expectedOrganisation);
    }

    /**
     * @dataProvider provideInvalidData
     */
    public function testCreateWithInvalidData(array $data): void
    {
        $organisationFactory = new OrganisationFactory();

        $this->expectException(\BadMethodCallException::class);

        $organisationFactory->createFromArray($data);
    }

    public function provideValidData(): iterable
    {
        yield [
            [
                'id' => 1,
            ],
            (new Organisation())
                ->setId(1)
                ->setUrl('')
                ->setAvatarUrl('')
                ->setGravatarId('')
                ->setLogin(''),
        ];

        yield [
            [
                'id' => 1,
                'url' => 'stephane.giberne.fr',
                'avatar_url' => 'stephane.giberne.fr/avatar.jpg',
                'gravatar_id' => 12345,
                'login' => 'sgiberne',
            ],
            (new Organisation())
                ->setId(1)
                ->setUrl('stephane.giberne.fr')
                ->setAvatarUrl('stephane.giberne.fr/avatar.jpg')
                ->setGravatarId('12345')
                ->setLogin('sgiberne'),
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
            ],
        ];
    }
}
