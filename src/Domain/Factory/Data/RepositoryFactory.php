<?php

namespace App\Domain\Factory\Data;

use App\Domain\Entity\Repository;

class RepositoryFactory
{
    /**
     * @param array<string, string|int> $data
     */
    public function createFromArray(array $data): Repository
    {
        if (!isset($data['id'])) {
            throw new \BadMethodCallException('An id must be defined');
        }

        return (new Repository())
            ->setId((int) $data['id'])
            ->setUrl(isset($data['url']) ? (string) $data['url'] : '')
            ->setName(isset($data['name']) ? (string) $data['name'] : '');
    }
}
