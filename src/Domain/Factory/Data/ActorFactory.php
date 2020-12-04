<?php

namespace App\Domain\Factory\Data;

use App\Domain\Entity\Actor;

class ActorFactory
{
    /**
     * @param array<string, string|int> $data
     */
    public function createFromArray(array $data): Actor
    {
        if (!isset($data['id'])) {
            throw new \BadMethodCallException('An id must be defined');
        }

        return (new Actor())
            ->setId((int) $data['id'])
            ->setUrl(isset($data['url']) ? (string) $data['url'] : '')
            ->setAvatarUrl(isset($data['avatar_url']) ? (string) $data['avatar_url'] : '')
            ->setDisplayLogin(isset($data['display_login']) ? (string) $data['display_login'] : '')
            ->setGravatarId(isset($data['gravatar_id']) ? (string) $data['gravatar_id'] : '')
            ->setLogin(isset($data['login']) ? (string) $data['login'] : '');
    }
}
