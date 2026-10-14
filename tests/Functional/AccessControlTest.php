<?php

namespace App\Tests\Functional;

class AccessControlTest extends DatabaseWebTestCase
{
    public function testAnonymousIsRedirectedToLoginForProfile(): void
    {
        $this->client->request('GET', '/profil');

        self::assertResponseRedirects('/login');
    }

    public function testAnonymousIsRedirectedToLoginForVolunteerArea(): void
    {
        $this->client->request('GET', '/gestion/animaux');

        self::assertResponseRedirects('/login');
    }

    public function testSimpleUserCannotAccessVolunteerArea(): void
    {
        $this->client->loginUser($this->createUser('simple@refuge.test'));

        $this->client->request('GET', '/gestion/animaux');

        self::assertResponseStatusCodeSame(403);
    }

    public function testVolunteerCanAccessVolunteerArea(): void
    {
        $this->client->loginUser($this->createUser('benevole@refuge.test', 'password', ['ROLE_VOLUNTEER']));

        $this->client->request('GET', '/gestion/animaux');

        self::assertResponseIsSuccessful();
    }

    public function testSimpleUserCannotAccessAdmin(): void
    {
        $this->client->loginUser($this->createUser('simple@refuge.test'));

        $this->client->request('GET', '/admin');

        self::assertResponseStatusCodeSame(403);
    }

    public function testAdminCanAccessAdmin(): void
    {
        $this->client->loginUser($this->createUser('admin@refuge.test', 'password', ['ROLE_ADMIN']));

        $this->client->request('GET', '/admin');

        self::assertResponseIsSuccessful();
    }
}
