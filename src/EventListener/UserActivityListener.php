<?php

namespace App\EventListener;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserActivityListener implements EventSubscriberInterface
{
    private const THROTTLE_SECONDS = 60;

    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $user = $this->tokenStorage->getToken()?->getUser();

        if (!$user instanceof User) {
            return;
        }

        $now = new \DateTimeImmutable();

        if (null !== $user->getLastSeenAt()
            && $user->getLastSeenAt() > $now->modify(sprintf('-%d seconds', self::THROTTLE_SECONDS))
        ) {
            return;
        }

        $user->setLastSeenAt($now);
        $this->entityManager->flush();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => 'onKernelRequest',
        ];
    }
}
