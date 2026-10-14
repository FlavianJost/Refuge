<?php

namespace App\Tests\Unit\Entity;

use App\Entity\AdoptionRequest;
use App\Enum\AdoptionRequestStatus;
use PHPUnit\Framework\TestCase;

class AdoptionRequestTest extends TestCase
{
    public function testNewRequestHasExpectedDefaults(): void
    {
        $request = new AdoptionRequest();

        self::assertNull($request->getId());
        self::assertNull($request->getAnimal());
        self::assertNull($request->getUser());
        self::assertNull($request->getProcessedBy());
        self::assertNull($request->getMessage());
        self::assertSame(AdoptionRequestStatus::PENDING, $request->getStatus());
        self::assertInstanceOf(\DateTimeImmutable::class, $request->getCreatedAt());
        self::assertNull($request->getUpdatedAt());
    }

    public function testSettersAreFluent(): void
    {
        $request = new AdoptionRequest();

        self::assertSame($request, $request->setStatus(AdoptionRequestStatus::ACCEPTED));
        self::assertSame($request, $request->setMessage('Je souhaite adopter cet animal.'));
        self::assertSame(AdoptionRequestStatus::ACCEPTED, $request->getStatus());
        self::assertSame('Je souhaite adopter cet animal.', $request->getMessage());
    }

    public function testTouchUpdatesTimestamp(): void
    {
        $request = new AdoptionRequest();

        self::assertNull($request->getUpdatedAt());
        $request->touch();
        self::assertInstanceOf(\DateTimeImmutable::class, $request->getUpdatedAt());
    }
}
