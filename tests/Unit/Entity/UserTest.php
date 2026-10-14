<?php

namespace App\Tests\Unit\Entity;

use App\Entity\AdoptionRequest;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testNewUserHasExpectedDefaults(): void
    {
        $user = new User();

        self::assertNull($user->getId());
        self::assertNull($user->getEmail());
        self::assertSame(['ROLE_USER'], $user->getRoles());
        self::assertNull($user->getPassword());
        self::assertNull($user->getFirstName());
        self::assertNull($user->getLastName());
        self::assertNull($user->getPhoto());
        self::assertInstanceOf(\DateTimeImmutable::class, $user->getCreatedAt());
        self::assertNull($user->getLastLoginAt());
        self::assertCount(0, $user->getAdoptionRequests());
        self::assertCount(0, $user->getProcessedRequests());
    }

    public function testGetRolesAlwaysContainsRoleUser(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_ADMIN']);

        self::assertSame(['ROLE_ADMIN', 'ROLE_USER'], $user->getRoles());
    }

    public function testAddRoleIgnoresDuplicates(): void
    {
        $user = new User();
        $user->addRole('ROLE_VOLUNTEER');
        $user->addRole('ROLE_VOLUNTEER');

        self::assertSame(['ROLE_USER', 'ROLE_VOLUNTEER'], $user->getRoles());
    }

    public function testGetUserIdentifierReturnsEmail(): void
    {
        $user = (new User())->setEmail('camille@refuge.test');

        self::assertSame('camille@refuge.test', $user->getUserIdentifier());
    }

    public function testGetFullNameCombinesFirstAndLastName(): void
    {
        $user = (new User())->setFirstName('Camille')->setLastName('Durand');

        self::assertSame('Camille Durand', $user->getFullName());
    }

    public function testGetFullNameFallsBackToEmail(): void
    {
        $user = (new User())->setEmail('camille@refuge.test');

        self::assertSame('camille@refuge.test', $user->getFullName());
    }

    public function testToStringReturnsFullName(): void
    {
        $user = (new User())->setFirstName('Camille')->setLastName('Durand');

        self::assertSame('Camille Durand', (string) $user);
    }

    public function testPhotoIsMutable(): void
    {
        $user = new User();

        self::assertSame($user, $user->setPhoto('user_123.png'));
        self::assertSame('user_123.png', $user->getPhoto());
        $user->setPhoto(null);
        self::assertNull($user->getPhoto());
    }

    public function testAddAdoptionRequestKeepsBothSidesInSync(): void
    {
        $user = new User();
        $request = new AdoptionRequest();

        $user->addAdoptionRequest($request);
        $user->addAdoptionRequest($request);

        self::assertCount(1, $user->getAdoptionRequests());
        self::assertTrue($user->getAdoptionRequests()->contains($request));
        self::assertSame($user, $request->getUser());

        $user->removeAdoptionRequest($request);

        self::assertCount(0, $user->getAdoptionRequests());
        self::assertNull($request->getUser());
    }

    public function testAddProcessedRequestKeepsBothSidesInSync(): void
    {
        $user = new User();
        $request = new AdoptionRequest();

        $user->addProcessedRequest($request);

        self::assertCount(1, $user->getProcessedRequests());
        self::assertSame($user, $request->getProcessedBy());

        $user->removeProcessedRequest($request);

        self::assertCount(0, $user->getProcessedRequests());
        self::assertNull($request->getProcessedBy());
    }
}
