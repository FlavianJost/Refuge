<?php

namespace App\Repository;

use App\Entity\Donation;
use App\Enum\DonationStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Donation>
 */
class DonationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Donation::class);
    }

    /**
     * Total des dons confirmés, en centimes d'euro.
     */
    public function getTotalAmount(): int
    {
        return (int) $this->createQueryBuilder('d')
            ->select('COALESCE(SUM(d.amount), 0)')
            ->andWhere('d.status = :status')
            ->setParameter('status', DonationStatus::PAID)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
