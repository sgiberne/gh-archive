<?php

namespace App\Domain\Repository;

use App\Domain\Entity\Repository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Repository>
 */
class RepositoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Repository::class);
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return parent::getEntityManager();
    }
}
