<?php

namespace App\Repository;

use App\Entity\Conversation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Conversation>
 */
class ConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conversation::class);
    }

    public function findOneForUser(User $user): ?Conversation
    {
        return $this->findOneBy(['user' => $user]);
    }

    /**
     * @return Conversation[]
     */
    public function findAllRecent(): array
    {
        return $this->createQueryBuilder('c')
            ->addSelect('u')
            ->join('c.user', 'u')
            ->orderBy('c.lastMessageAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countUnreadForStaff(): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COALESCE(SUM(c.unreadForStaff), 0)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
