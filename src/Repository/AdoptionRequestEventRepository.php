<?php

namespace App\Repository;

use App\Entity\AdoptionRequestEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AdoptionRequestEvent>
 */
class AdoptionRequestEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdoptionRequestEvent::class);
    }
}
