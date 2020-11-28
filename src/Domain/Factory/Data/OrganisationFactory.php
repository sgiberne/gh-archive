<?php

namespace App\Domain\Factory\Data;

use App\Domain\Entity\Organisation;

class OrganisationFactory
{
    public function createFromArray(array $data): Organisation
    {
        if (!isset($data['id'])) {
            throw new \BadMethodCallException('An id must be defined');
        }

        return (new Organisation())
            ->setId($data['id'])
            ->setLogin($data['login'] ?? '')
            ->setGravatarId($data['gravatar_id'] ?? '')
            ->setAvatarUrl($data['avatar_url'] ?? '')
            ->setUrl($data['url'] ?? '');
    }
}
