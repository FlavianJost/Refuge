<?php

namespace App\Tests\Unit\Service;

use App\Entity\AdoptionRequest;
use App\Entity\Animal;
use App\Entity\User;
use App\Enum\AdoptionRequestStatus;
use App\Enum\AnimalStatus;
use App\Service\AdoptionRequestProcessor;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class AdoptionRequestProcessorTest extends TestCase
{
    public function testAcceptValidatesRequestAndAnimal(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('flush');

        $animal = (new Animal())->setName('Rex');
        $request = new AdoptionRequest();
        $animal->addAdoptionRequest($request);
        $volunteer = (new User())->setEmail('benevole@refuge.test');

        (new AdoptionRequestProcessor($entityManager))
            ->changeStatus($request, AdoptionRequestStatus::ACCEPTED, $volunteer);

        self::assertSame(AdoptionRequestStatus::ACCEPTED, $request->getStatus());
        self::assertSame($volunteer, $request->getProcessedBy());
        self::assertSame(AnimalStatus::ADOPTED, $animal->getStatus());
        self::assertNotNull($request->getUpdatedAt());
    }

    public function testAcceptRejectsCompetingRequests(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('flush');

        $animal = (new Animal())->setName('Rex');
        $accepted = new AdoptionRequest();
        $competing = new AdoptionRequest();
        $animal->addAdoptionRequest($accepted);
        $animal->addAdoptionRequest($competing);
        $volunteer = (new User())->setEmail('benevole@refuge.test');

        (new AdoptionRequestProcessor($entityManager))
            ->changeStatus($accepted, AdoptionRequestStatus::ACCEPTED, $volunteer);

        self::assertSame(AdoptionRequestStatus::REJECTED, $competing->getStatus());
        self::assertSame($volunteer, $competing->getProcessedBy());
        self::assertNotNull($competing->getUpdatedAt());
    }

    public function testRejectLeavesAnimalAvailable(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('flush');

        $animal = (new Animal())->setName('Rex');
        $request = new AdoptionRequest();
        $animal->addAdoptionRequest($request);
        $volunteer = (new User())->setEmail('benevole@refuge.test');

        (new AdoptionRequestProcessor($entityManager))
            ->changeStatus($request, AdoptionRequestStatus::REJECTED, $volunteer);

        self::assertSame(AdoptionRequestStatus::REJECTED, $request->getStatus());
        self::assertSame($volunteer, $request->getProcessedBy());
        self::assertSame(AnimalStatus::AVAILABLE, $animal->getStatus());
    }

    public function testReopenRestoresAnimalToAvailable(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('flush');

        $animal = (new Animal())->setName('Rex');
        $animal->setStatus(AnimalStatus::ADOPTED);
        $request = new AdoptionRequest();
        $animal->addAdoptionRequest($request);
        $request->setStatus(AdoptionRequestStatus::ACCEPTED);
        $volunteer = (new User())->setEmail('benevole@refuge.test');

        (new AdoptionRequestProcessor($entityManager))
            ->changeStatus($request, AdoptionRequestStatus::IN_PROGRESS, $volunteer);

        self::assertSame(AdoptionRequestStatus::IN_PROGRESS, $request->getStatus());
        self::assertNull($request->getProcessedBy());
        self::assertSame(AnimalStatus::AVAILABLE, $animal->getStatus());
    }

    public function testReopenKeepsAnimalAdoptedWhenAnotherRequestAccepted(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('flush');

        $animal = (new Animal())->setName('Rex');
        $animal->setStatus(AnimalStatus::ADOPTED);
        $first = new AdoptionRequest();
        $second = new AdoptionRequest();
        $animal->addAdoptionRequest($first);
        $animal->addAdoptionRequest($second);
        $first->setStatus(AdoptionRequestStatus::ACCEPTED);
        $second->setStatus(AdoptionRequestStatus::ACCEPTED);
        $volunteer = (new User())->setEmail('benevole@refuge.test');

        (new AdoptionRequestProcessor($entityManager))
            ->changeStatus($first, AdoptionRequestStatus::IN_PROGRESS, $volunteer);

        self::assertSame(AdoptionRequestStatus::IN_PROGRESS, $first->getStatus());
        self::assertSame(AnimalStatus::ADOPTED, $animal->getStatus());
    }
}
