<?php

namespace App\Domain\Factory\Data;

use App\Domain\Entity\Actor;

class ActorFactory
{
    public function createFromArray(array $data): Actor
    {
        return (new Actor())
            ->setId($data['id'])
            ->setUrl($data['url'] ?? '')
            ->setAvatarUrl($data['avatar_url'] ?? '')
            ->setDisplayLogin($data['display_login'] ?? '')
            ->setGravatarId($data['gravatar_id'] ?? '')
            ->setLogin($data['login'] ?? '');
    }
}
