<?php

namespace App\Repository;

use App\Entity\Animal;
use App\Enum\AnimalStatus;
use App\Enum\Species;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Animal>
 */
class AnimalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Animal::class);
    }

    /**
     * @return Animal[]
     */
    public function findAvailable(): array
    {
        return $this->findByFilters(AnimalStatus::AVAILABLE, null);
    }

    /**
     * @return Animal[]
     */
    public function findLatest(int $limit): array
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Animal[]
     */
    public function findByFilters(?AnimalStatus $status = null, ?Species $species = null): array
    {
        $qb = $this->createQueryBuilder('a')->orderBy('a.name', 'ASC');

        if (null !== $status) {
            $qb->andWhere('a.status = :status')->setParameter('status', $status);
        }

        if (null !== $species) {
            $qb->andWhere('a.species = :species')->setParameter('species', $species);
        }

        return $qb->getQuery()->getResult();
    }
}
