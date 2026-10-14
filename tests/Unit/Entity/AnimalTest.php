<?php

namespace App\Tests\Unit\Entity;

use App\Entity\AdoptionRequest;
use App\Entity\Animal;
use App\Enum\AnimalStatus;
use App\Enum\Species;
use PHPUnit\Framework\TestCase;

class AnimalTest extends TestCase
{
    public function testNewAnimalHasExpectedDefaults(): void
    {
        $animal = new Animal();

        self::assertNull($animal->getId());
        self::assertNull($animal->getName());
        self::assertNull($animal->getSlug());
        self::assertSame(Species::OTHER, $animal->getSpecies());
        self::assertSame(AnimalStatus::AVAILABLE, $animal->getStatus());
        self::assertNull($animal->getBreed());
        self::assertNull($animal->getAge());
        self::assertNull($animal->getDescription());
        self::assertNull($animal->getPhoto());
        self::assertInstanceOf(\DateTimeImmutable::class, $animal->getCreatedAt());
        self::assertNull($animal->getUpdatedAt());
        self::assertCount(0, $animal->getAdoptionRequests());
        self::assertTrue($animal->isAvailable());
    }

    public function testIsAvailableFollowsStatus(): void
    {
        $animal = new Animal();
        $animal->setStatus(AnimalStatus::ADOPTED);

        self::assertFalse($animal->isAvailable());
    }

    public function testTouchUpdatesTimestamp(): void
    {
        $animal = new Animal();

        self::assertNull($animal->getUpdatedAt());
        $animal->touch();
        self::assertInstanceOf(\DateTimeImmutable::class, $animal->getUpdatedAt());
    }

    public function testToStringReturnsName(): void
    {
        $animal = (new Animal())->setName('Rex');

        self::assertSame('Rex', (string) $animal);
    }

    public function testAddAdoptionRequestKeepsBothSidesInSync(): void
    {
        $animal = new Animal();
        $request = new AdoptionRequest();

        $animal->addAdoptionRequest($request);
        $animal->addAdoptionRequest($request);

        self::assertCount(1, $animal->getAdoptionRequests());
        self::assertSame($animal, $request->getAnimal());

        $animal->removeAdoptionRequest($request);

        self::assertCount(0, $animal->getAdoptionRequests());
        self::assertNull($request->getAnimal());
    }
}
