<?php

namespace App\Domain\Factory\Data;

use App\Domain\Entity\Repository;

class RepositoryFactory
{
    public function createFromArray(array $data): Repository
    {
        if (!isset($data['id'])) {
            throw new \BadMethodCallException('An id must be defined');
        }

        return (new Repository())
            ->setId($data['id'])
            ->setUrl($data['url'] ?? '')
            ->setName($data['name'] ?? '');
    }
}
