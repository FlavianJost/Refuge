<?php

namespace App\Tests\Functional;

use App\Entity\Animal;
use App\Entity\User;
use App\Enum\AnimalStatus;
use App\Enum\Species;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Base class for functional tests requiring the database.
 *
 * Every test runs inside a transaction that is rolled back afterwards so the
 * database is left untouched between tests.
 */
abstract class DatabaseWebTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->disableReboot();
        // Stateless CSRF tokens are validated using the Origin/Referer headers.
        $this->client->setServerParameter('HTTP_ORIGIN', 'http://localhost');

        $registry = static::getContainer()->get('doctrine');
        $this->entityManager = $registry->getManager();

        $this->entityManager->getConnection()->beginTransaction();
    }

    protected function tearDown(): void
    {
        if (isset($this->entityManager)) {
            $connection = $this->entityManager->getConnection();
            if ($connection->isTransactionActive()) {
                $connection->rollBack();
            }
            $this->entityManager->close();
        }

        parent::tearDown();
    }

    protected function createUser(string $email, string $plainPassword = 'password', array $roles = []): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setFirstName('Test');
        $user->setLastName('Utilisateur');
        if ([] !== $roles) {
            $user->setRoles($roles);
        }

        $hasher = static::getContainer()->get('security.user_password_hasher');
        \assert($hasher instanceof UserPasswordHasherInterface);
        $user->setPassword($hasher->hashPassword($user, $plainPassword));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    protected function createAnimal(
        string $name,
        string $slug,
        Species $species = Species::DOG,
        AnimalStatus $status = AnimalStatus::AVAILABLE,
    ): Animal {
        $animal = new Animal();
        $animal->setName($name);
        $animal->setSlug($slug);
        $animal->setSpecies($species);
        $animal->setStatus($status);

        $this->entityManager->persist($animal);
        $this->entityManager->flush();

        return $animal;
    }
}
