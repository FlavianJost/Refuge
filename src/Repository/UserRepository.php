<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Indique si au moins un membre du refuge (bénévole ou administrateur) est en ligne.
     */
    public function hasOnlineStaff(int $thresholdSeconds = 300): bool
    {
        $since = new \DateTimeImmutable(sprintf('-%d seconds', $thresholdSeconds));

        $count = (int) $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->andWhere('u.lastSeenAt >= :since')
            ->andWhere('u.roles LIKE :role')
            ->setParameter('since', $since)
            ->setParameter('role', '%ROLE_VOLUNTEER%')
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }
}
