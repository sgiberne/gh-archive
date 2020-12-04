<?php

namespace App\Domain\Repository;

use App\Domain\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
final class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    /**
     * @param array<string, string|\DateTime> $criteria
     * @param null|string $sort
     * @param null|string $order
     * @param int|null $limit
     * @param int|null $offset
     * @return QueryBuilder
     * @throws \JsonException
     */
    public function findByCriteriaQueryBuilder(array $criteria, string $sort = null, string $order = null, int $limit = null, int $offset = null): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('e');

        if (null !== $limit) {
            $queryBuilder->setMaxResults($limit);
        }

        if (null !== $offset) {
            $queryBuilder->setFirstResult($offset);
        }

        if (null !== $sort) {
            $queryBuilder->orderBy("e.$sort", $order);
        }

        foreach ($criteria as $key => $criterion) {
            if ('createdAt' === $key && $criterion instanceof \DateTime) {
                // fait la recherche sur la journée
                $startDate = clone $criterion->setTime(0, 0, 0);
                $endDate = clone $criterion->setTime(23, 59, 59);
                $queryBuilder
                    ->andWhere("e.$key >= :startDate")
                    ->andWhere("e.$key <= :endDate")
                    ->setParameter('startDate', $startDate)
                    ->setParameter('endDate', $endDate);
            } elseif ('payload' === $key && is_string($criterion)) {
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

    /**
     * @param array<string, string|\DateTime> $criteria
     * @param null|string $sort
     * @param null|string $order
     * @param int|null $limit
     * @param int|null $offset
     * @return array<int, Event>
     * @throws \JsonException
     */
    public function findByCriteria(array $criteria, string $sort = null, string $order = null, int $limit = null, int $offset = null): array
    {
        return $this->findByCriteriaQueryBuilder($criteria, $sort, $order, $limit, $offset)->getQuery()->getResult();
    }

    /**
     * @param array<string, string|\DateTime> $criteria
     * @return int
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \JsonException
     */
    public function countByCriteria(array $criteria): int
    {
        return $this
            ->findByCriteriaQueryBuilder($criteria)
            ->select('count(e.id)')
            ->getQuery()->getSingleScalarResult();
    }
}
