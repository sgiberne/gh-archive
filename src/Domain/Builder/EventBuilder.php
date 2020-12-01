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

    public function build(array $data): ?Event
    {
        if (empty($data['id'])) {
            return null;
        }

        return (new Event())
            ->setId($data['id'])
            ->setRepository(isset($data['repo']) ? $this->repositoryBuilder->build($data['repo']) : null)
            ->setOrganisation(isset($data['org']) ? $this->organisationBuilder->build($data['org']) : null)
            ->setActor(isset($data['actor']) ? $this->actorBuilder->build($data['actor']) : null)
            ->setType($data['type'] ?? '')
            ->setPayload($data['payload'] ?? [])
            ->setPublic($data['public'] ?? true)
            ->setCreatedAt(isset($data['created_at']) ? new \DateTime($data['created_at']) : null);
    }
}
