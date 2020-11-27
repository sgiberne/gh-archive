<?php

namespace App\Domain\Builder;

use App\Domain\Entity\Actor;
use App\Domain\Factory\Data\ActorFactory;
use App\Domain\Repository\ActorRepository;

class ActorBuilder
{
    private ActorRepository $actorRepository;
    private ActorFactory $actorFactory;

    public function __construct(
        ActorRepository $actorRepository,
        ActorFactory $actorFactory
    ) {
        $this->actorRepository = $actorRepository;
        $this->actorFactory = $actorFactory;
    }

    public function build(array $data): ?Actor
    {
        if (isset($data['id'])) {
            $actor = $this->actorRepository->find($data['id']);
            if ($actor instanceof Actor) {
                return $actor;
            }

            $actor = $this->actorFactory->createFromArray($data);
            if ($actor instanceof Actor) {
                return $actor;
            }
        }

        return null;
    }
}
