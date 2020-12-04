<?php

namespace App\Domain\Builder;

use App\Domain\Entity\Event;

class EventBuilder
{
    private ActorBuilder $actorBuilder;
    private OrganisationBuilder $organisationBuilder;
    private RepositoryBuilder $repositoryBuilder;

    public function __construct(
        ActorBuilder $actorBuilder,
        OrganisationBuilder $organisationBuilder,
        RepositoryBuilder $repositoryBuilder
    ) {
        $this->actorBuilder = $actorBuilder;
        $this->organisationBuilder = $organisationBuilder;
        $this->repositoryBuilder = $repositoryBuilder;
    }

    /**
     * @param array<string, int|string|array> $data
     *
     * @throws \Exception
     */
    public function build(array $data): ?Event
    {
        if (empty($data['id']) || !is_numeric($data['id'])) {
            return null;
        }

        $repository = isset($data['repo']) && !empty($data['repo']) && is_array($data['repo']) ? $this->repositoryBuilder->build($data['repo']) : null;
        $organisation = isset($data['org']) && !empty($data['org']) && is_array($data['org']) ? $this->organisationBuilder->build($data['org']) : null;
        $actor = isset($data['actor']) && !empty($data['actor']) && is_array($data['actor']) ? $this->actorBuilder->build($data['actor']) : null;

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
