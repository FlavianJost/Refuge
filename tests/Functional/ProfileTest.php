<?php

namespace App\Tests\Functional;

use App\Entity\User;

class ProfileTest extends DatabaseWebTestCase
{
    /** @var list<string> */
    private array $tempFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        $this->tempFiles = [];

        parent::tearDown();
    }

    public function testProfilePageDisplaysUserInformation(): void
    {
        $this->client->loginUser($this->createUser('camille@refuge.test'));

        $this->client->request('GET', '/profil');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Mon profil');
        self::assertSelectorTextContains('body', 'camille@refuge.test');
        self::assertSelectorTextContains('body', 'Test Utilisateur');
    }

    public function testEditFormIsAvailable(): void
    {
        $this->client->loginUser($this->createUser('camille@refuge.test'));

        $this->client->request('GET', '/profil/modifier');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Modifier mon profil');
    }

    public function testProfileCanBeEdited(): void
    {
        $user = $this->createUser('camille@refuge.test');
        $this->client->loginUser($user);

        $crawler = $this->client->request('GET', '/profil/modifier');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Enregistrer')->form();
        $form['profile[firstName]'] = 'Camille';
        $form['profile[lastName]'] = 'Durand';
        $form['profile[email]'] = 'camille.durand@refuge.test';
        $this->client->submit($form);

        self::assertResponseRedirects('/profil');

        $this->entityManager->clear();
        $updated = $this->entityManager->getRepository(User::class)->find($user->getId());

        self::assertSame('camille.durand@refuge.test', $updated->getEmail());
        self::assertSame('Camille', $updated->getFirstName());
        self::assertSame('Durand', $updated->getLastName());
    }

    public function testProfilePhotoCanBeUploaded(): void
    {
        $user = $this->createUser('camille@refuge.test');
        $this->client->loginUser($user);

        $crawler = $this->client->request('GET', '/profil/modifier');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Enregistrer')->form();
        $form['profile[firstName]'] = 'Camille';
        $form['profile[lastName]'] = 'Durand';
        $form['profile[email]'] = 'camille@refuge.test';
        $form['profile[photoFile]']->upload($this->createPngFile());
        $this->client->submit($form);

        self::assertResponseRedirects('/profil');

        $this->entityManager->clear();
        $updated = $this->entityManager->getRepository(User::class)->find($user->getId());

        self::assertNotNull($updated->getPhoto());
        self::assertStringStartsWith('user_', $updated->getPhoto());
    }

    private function createPngFile(): string
    {
        $path = sys_get_temp_dir().'/refuge_profile_'.uniqid('', true).'.png';
        file_put_contents($path, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII='));
        $this->tempFiles[] = $path;

        return $path;
    }
}
