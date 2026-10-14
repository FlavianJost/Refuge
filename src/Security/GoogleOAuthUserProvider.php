<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Charge (ou crée) un utilisateur local à partir d'une réponse Google.
 */
class GoogleOAuthUserProvider implements OAuthAwareUserProviderInterface
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function loadUserByOAuthUserResponse(UserResponseInterface $response): UserInterface
    {
        $email = $response->getEmail();

        if (null === $email || '' === $email) {
            throw new UserNotFoundException('Google n\'a pas fourni d\'adresse email.');
        }

        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (null === $user) {
            $user = (new User())
                ->setEmail($email)
                ->setFirstName($response->getFirstName())
                ->setLastName($response->getLastName());

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return $user;
        }

        $this->enrichNames($user, $response);

        return $user;
    }

    private function enrichNames(User $user, UserResponseInterface $response): void
    {
        $changed = false;

        if (null === $user->getFirstName() && null !== $response->getFirstName()) {
            $user->setFirstName($response->getFirstName());
            $changed = true;
        }

        if (null === $user->getLastName() && null !== $response->getLastName()) {
            $user->setLastName($response->getLastName());
            $changed = true;
        }

        if ($changed) {
            $this->entityManager->flush();
        }
    }
}
