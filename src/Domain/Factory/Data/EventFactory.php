<?php

namespace App\Domain\Factory\Data;

use App\Domain\Entity\Event;

class EventFactory
{
    private ActorFactory $actorFactory;
    private RepositoryFactory $repositoryFactor;
    private OrganisationFactory $organisationFactory;

    public function __construct(ActorFactory $actorFactory, RepositoryFactory $repositoryFactory, OrganisationFactory $organisationFactory)
    {
        $this->actorFactory = $actorFactory;
        $this->repositoryFactor = $repositoryFactory;
        $this->organisationFactory = $organisationFactory;
    }

    public function createFromArray(array $data): Event
    {
        $actor = isset($data['actor']) && !empty($data['actor']) ? $this->actorFactory->createFromArray($data['actor']) : null;
        $repository = isset($data['repo']) && !empty($data['repo']) ? $this->repositoryFactor->createFromArray($data['repo']) : null;
        $organisation = isset($data['org']) && !empty($data['org']) ? $this->organisationFactory->createFromArray($data['org']) : null;

        return (new Event())
            ->setId($data['id'])
            ->setRepository($repository)
            ->setOrganisation($organisation)
            ->setActor($actor)
            ->setType($data['type'] ?? '')
            ->setPayload($data['payload'] ?? [])
            ->setPublic($data['public'] ?? true)
            ->setCreatedAt($data['created_at'] ? new \DateTime($data['created_at']) : null);
    }
}
