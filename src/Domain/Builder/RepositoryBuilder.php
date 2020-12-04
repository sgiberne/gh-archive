<?php

namespace App\Domain\Builder;

use App\Domain\Entity\Repository;
use App\Domain\Factory\Data\RepositoryFactory;
use App\Domain\Repository\RepositoryRepository;

class RepositoryBuilder
{
    private RepositoryRepository $repositoryRepository;
    private RepositoryFactory $repositoryFactory;

    public function __construct(
        RepositoryRepository $repositoryRepository,
        RepositoryFactory $repositoryFactory
    ) {
        $this->repositoryRepository = $repositoryRepository;
        $this->repositoryFactory = $repositoryFactory;
    }

    /**
     * @param string[] $data
     */
    public function build(array $data): ?Repository
    {
        if (isset($data['id'])) {
            $repository = $this->repositoryRepository->find($data['id']);
            if ($repository instanceof Repository) {
                return $repository;
            }

            $repository = $this->repositoryFactory->createFromArray($data);
            if ($repository instanceof Repository) {
                return $repository;
            }
        }

        return null;
    }
}
