<?php

namespace App\Tests\Functional;

class AuthenticationTest extends DatabaseWebTestCase
{
    public function testLoginPageIsAvailable(): void
    {
        $this->client->request('GET', '/login');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Connexion');
    }

    public function testLoginWithValidCredentialsRedirectsToHomepage(): void
    {
        $this->createUser('camille@refuge.test', 'secret123');

        $this->client->request('GET', '/login');
        $this->client->submitForm('Se connecter', [
            '_username' => 'camille@refuge.test',
            '_password' => 'secret123',
        ]);

        self::assertResponseRedirects('/');

        $this->client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('nav', 'Test Utilisateur');
    }

    public function testLoginWithInvalidCredentialsDisplaysAnError(): void
    {
        $this->createUser('camille@refuge.test', 'secret123');

        $this->client->request('GET', '/login');
        $this->client->submitForm('Se connecter', [
            '_username' => 'camille@refuge.test',
            '_password' => 'wrong-password',
        ]);

        self::assertResponseRedirects('/login');

        $this->client->followRedirect();
        self::assertSelectorExists('.alert-danger');
    }

    public function testLogoutReturnsToHomepage(): void
    {
        $this->client->loginUser($this->createUser('camille@refuge.test'));

        $this->client->request('GET', '/profil');
        self::assertResponseIsSuccessful();

        $this->client->request('GET', '/deconnexion');
        self::assertResponseRedirects('/');
    }
}
