<?php

namespace App\Domain\Factory\Data;

use App\Domain\Entity\Organisation;

class OrganisationFactory
{
    public function createFromArray(array $data): Organisation
    {
        return (new Organisation())
            ->setId($data['id'])
            ->setLogin($data['login'] ?? '')
            ->setGravatarId($data['gravatar_id'] ?? '')
            ->setAvatarUrl($data['avatar_url'] ?? '')
            ->setUrl($data['url'] ?? '');
    }
}
