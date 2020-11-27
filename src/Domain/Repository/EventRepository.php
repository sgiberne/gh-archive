<?php

namespace App\Domain\Repository;

use App\Domain\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Constraints\DateTime;

final class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function findByCriteriaQueryBuilder(array $criteria, string $sort = null, string $order = null, int $limit = null, int $offset = null): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('e');

        if ($limit !== null) {
            $queryBuilder->setMaxResults($limit);
        }

        if ($offset !== null) {
            $queryBuilder->setFirstResult($offset);
        }

        if ($sort !== null) {
            $queryBuilder->orderBy("e.$sort", $order);
        }

        foreach ($criteria as $key => $criterion) {
            if ($key === 'createdAt' && $criterion instanceof DateTime) {
                // fait la recherche sur la journée
                $startDate = clone $criterion->setTime(0, 0, 0);
                $endDate = clone $criterion->setTime(23, 59, 59);
                $queryBuilder
                    ->andWhere("e.$key >= :startDate")
                    ->andWhere("e.$key <= :endDate")
                    ->setParameter('startDate', $startDate)
                    ->setParameter('endDate', $endDate);
            } else if ($key === 'payload') {
                // cherche dans le json du payload
                $dataCriterion = json_decode($criterion, true, 512, JSON_THROW_ON_ERROR);
                $candidate = current($dataCriterion);
                $path = key($dataCriterion);

                $queryBuilder->andWhere("JSON_SEARCH(e.$key, 'all', '$candidate', '\', '$.$path') IS NOT NULL");
            } else {
                $queryBuilder
                    ->andWhere("e.$key = :$key")
                    ->setParameter($key, $criterion);
            }
        }

        return $queryBuilder;
    }

    public function findByCriteria(array $criteria, string $sort = null, string $order = null, int $limit = null, int $offset = null): array
    {
        return $this->findByCriteriaQueryBuilder($criteria, $sort, $order, $limit, $offset)->getQuery()->getResult();
    }

    public function countByCriteria(array $criteria): int
    {
        return $this
            ->findByCriteriaQueryBuilder($criteria)
            ->select('count(e.id)')
            ->getQuery()->getSingleScalarResult();
    }
}
