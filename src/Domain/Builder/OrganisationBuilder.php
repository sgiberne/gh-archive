<?php

namespace App\Domain\Builder;

use App\Domain\Entity\Organisation;
use App\Domain\Factory\Data\OrganisationFactory;
use App\Domain\Repository\OrganisationRepository;

class OrganisationBuilder
{
    private OrganisationRepository $organisationRepository;
    private OrganisationFactory $organisationFactory;

    public function __construct(
        OrganisationRepository $organisationRepository,
        OrganisationFactory $organisationFactory
    ) {
        $this->organisationRepository = $organisationRepository;
        $this->organisationFactory = $organisationFactory;
    }

    public function build(array $data): ?Organisation
    {
        if (isset($data['id'])) {
            $organisation = $this->organisationRepository->find($data['id']);
            if ($organisation instanceof Organisation) {
                return $organisation;
            }

            $organisation = $this->organisationFactory->createFromArray($data);
            if ($organisation instanceof Organisation) {
                return $organisation;
            }
        }

        return null;
    }
}
