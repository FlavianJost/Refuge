<?php

namespace App\Repository;

use App\Entity\AdoptionRequest;
use App\Enum\AdoptionRequestStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AdoptionRequest>
 */
class AdoptionRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdoptionRequest::class);
    }

    /**
     * @return AdoptionRequest[]
     */
    public function findByStatus(?AdoptionRequestStatus $status = null): array
    {
        $qb = $this->createQueryBuilder('r')
            ->addSelect('a')
            ->leftJoin('r.animal', 'a')
            ->orderBy('r.createdAt', 'DESC');

        if (null !== $status) {
            $qb->andWhere('r.status = :status')->setParameter('status', $status);
        }

        return $qb->getQuery()->getResult();
    }

    public function countPending(): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->andWhere('r.status = :status')
            ->setParameter('status', AdoptionRequestStatus::PENDING)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
