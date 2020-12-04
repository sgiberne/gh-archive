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

    /**
     * @param array<string, string|int|array> $data
     *
     * @throws \Exception
     */
    public function createFromArray(array $data): Event
    {
        if (!isset($data['id']) || !is_numeric($data['id'])) {
            throw new \BadMethodCallException('An id must be defined and must be a numeric');
        }

        $actor = isset($data['actor']) && !empty($data['actor']) && is_array($data['actor']) ? $this->actorFactory->createFromArray($data['actor']) : null;
        $repository = isset($data['repo']) && !empty($data['repo']) && is_array($data['repo']) ? $this->repositoryFactor->createFromArray($data['repo']) : null;
        $organisation = isset($data['org']) && !empty($data['org']) && is_array($data['org']) ? $this->organisationFactory->createFromArray($data['org']) : null;

        $event = (new Event())
            ->setId((int) $data['id'])
            ->setRepository($repository)
            ->setOrganisation($organisation)
            ->setActor($actor)
            ->setPublic(isset($data['public']) ? (bool) $data['public'] : false);

        if (isset($data['payload']) && is_array($data['payload'])) {
            $event->setPayload($data['payload']);
        }

        if (isset($data['type']) && is_string($data['type'])) {
            $event->setType($data['type']);
        }

        if (isset($data['created_at']) && is_string($data['created_at'])) {
            $event->setCreatedAt(new \DateTime($data['created_at']));
        }

        return $event;
    }
}
