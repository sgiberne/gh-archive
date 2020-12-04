<?php

namespace App\Domain\Factory\Data;

use App\Domain\Entity\Organisation;

class OrganisationFactory
{
    /**
     * @param array<string, string|int> $data
     */
    public function createFromArray(array $data): Organisation
    {
        if (!isset($data['id'])) {
            throw new \BadMethodCallException('An id must be defined');
        }

        return (new Organisation())
            ->setId((int) $data['id'])
            ->setLogin(isset($data['login']) ? (string) $data['login'] : '')
            ->setGravatarId(isset($data['gravatar_id']) ? (string) $data['gravatar_id'] : '')
            ->setAvatarUrl(isset($data['avatar_url']) ? (string) $data['avatar_url'] : '')
            ->setUrl(isset($data['url']) ? (string) $data['url'] : '');
    }
}
