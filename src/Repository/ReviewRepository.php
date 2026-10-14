<?php

namespace App\Repository;

use App\Entity\Review;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Review>
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    /**
     * @return Review[]
     */
    public function findLatest(int $limit = 10): array
    {
        return $this->createQueryBuilder('r')
            ->addSelect('u')
            ->leftJoin('r.user', 'u')
            ->orderBy('r.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Review[]
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.user = :user')
            ->setParameter('user', $user)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getAverageRating(): ?float
    {
        $average = $this->createQueryBuilder('r')
            ->select('AVG(r.rating)')
            ->getQuery()
            ->getSingleScalarResult();

        return null === $average ? null : (float) $average;
    }
}
