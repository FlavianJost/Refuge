<?php

namespace App\Tests\Functional;

use App\Entity\User;

class RegistrationTest extends DatabaseWebTestCase
{
    public function testRegistrationPageIsAvailable(): void
    {
        $this->client->request('GET', '/inscription');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Créer un compte');
    }

    public function testRegistrationCreatesUserAndRedirectsToLogin(): void
    {
        $this->client->request('GET', '/inscription');
        $this->client->submitForm("S'inscrire", [
            'registration_form[firstName]' => 'Nina',
            'registration_form[lastName]' => 'Petit',
            'registration_form[email]' => 'nina@refuge.test',
            'registration_form[plainPassword]' => 'motdepasse',
        ]);

        self::assertResponseRedirects('/login');

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'nina@refuge.test']);

        self::assertInstanceOf(User::class, $user);
        self::assertSame('Nina', $user->getFirstName());
        self::assertSame(['ROLE_USER'], $user->getRoles());
        self::assertNotSame('motdepasse', $user->getPassword());
    }

    public function testRegistrationRejectsInvalidData(): void
    {
        $this->client->request('GET', '/inscription');
        $this->client->submitForm("S'inscrire", [
            'registration_form[email]' => 'nina@refuge.test',
            'registration_form[plainPassword]' => '123',
        ]);

        self::assertResponseStatusCodeSame(422);

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'nina@refuge.test']);
        self::assertNull($user);
    }
}
