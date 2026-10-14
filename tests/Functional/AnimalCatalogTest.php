<?php

namespace App\Tests\Functional;

use App\Enum\Species;

class AnimalCatalogTest extends DatabaseWebTestCase
{
    public function testCatalogListsAvailableAnimals(): void
    {
        $this->createAnimal('Rex', 'rex');
        $this->createAnimal('Milo', 'milo', Species::CAT);

        $this->client->request('GET', '/animaux');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'Rex');
        self::assertSelectorTextContains('body', 'Milo');
    }

    public function testAnimalDetailPageIsResolvedBySlug(): void
    {
        $this->createAnimal('Rex', 'rex');

        $this->client->request('GET', '/animaux/rex');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Rex');
    }

    public function testCatalogCanBeFilteredBySpecies(): void
    {
        $this->createAnimal('Rex', 'rex', Species::DOG);
        $this->createAnimal('Milo', 'milo', Species::CAT);

        $crawler = $this->client->request('GET', '/animaux?species=cat');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'Milo');
        self::assertStringNotContainsString('Rex', $crawler->html());
    }

    public function testUnknownAnimalReturnsNotFound(): void
    {
        $this->client->request('GET', '/animaux/animal-inexistant');

        self::assertResponseStatusCodeSame(404);
    }
}
