<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomePageTest extends WebTestCase
{
    public function testHomePageIsAvailable(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Trouvez votre nouveau compagnon');
        self::assertSelectorTextContains('a.navbar-brand', 'Refuge');
    }

    public function testNavigationLinksToAnimalCatalog(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSame(1, $crawler->selectLink('Voir les animaux')->count());
    }
}
