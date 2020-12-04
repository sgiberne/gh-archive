<?php

namespace App\Domain\Repository;

use App\Domain\Entity\Organisation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Organisation>
 */
class OrganisationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Organisation::class);
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return parent::getEntityManager();
    }
}
