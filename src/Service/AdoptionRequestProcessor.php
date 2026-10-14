<?php

namespace App\Service;

use App\Entity\AdoptionRequest;
use App\Entity\User;
use App\Enum\AdoptionRequestStatus;
use App\Enum\AnimalStatus;
use Doctrine\ORM\EntityManagerInterface;

class AdoptionRequestProcessor
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function changeStatus(AdoptionRequest $request, AdoptionRequestStatus $status, User $volunteer): void
    {
        match ($status) {
            AdoptionRequestStatus::ACCEPTED => $this->accept($request, $volunteer),
            AdoptionRequestStatus::REJECTED => $this->reject($request, $volunteer),
            default => $this->reopen($request, $status),
        };

        $this->entityManager->flush();
    }

    private function accept(AdoptionRequest $request, User $volunteer): void
    {
        $now = new \DateTimeImmutable();

        $request->setStatus(AdoptionRequestStatus::ACCEPTED)
            ->setProcessedBy($volunteer)
            ->setProcessedAt($now);

        $animal = $request->getAnimal();

        if (null === $animal) {
            return;
        }

        $animal->setStatus(AnimalStatus::ADOPTED);

        foreach ($animal->getAdoptionRequests() as $other) {
            if ($other !== $request && AdoptionRequestStatus::REJECTED !== $other->getStatus()) {
                $other->setStatus(AdoptionRequestStatus::REJECTED)
                    ->setProcessedBy($volunteer)
                    ->setProcessedAt($now);
            }
        }
    }

    private function reject(AdoptionRequest $request, User $volunteer): void
    {
        $request->setStatus(AdoptionRequestStatus::REJECTED)
            ->setProcessedBy($volunteer)
            ->setProcessedAt(new \DateTimeImmutable());
    }

    private function reopen(AdoptionRequest $request, AdoptionRequestStatus $status): void
    {
        $request->setStatus($status)
            ->setProcessedBy(null)
            ->setProcessedAt(null);

        $animal = $request->getAnimal();

        if (null === $animal || AnimalStatus::ADOPTED !== $animal->getStatus()) {
            return;
        }

        foreach ($animal->getAdoptionRequests() as $other) {
            if (AdoptionRequestStatus::ACCEPTED === $other->getStatus()) {
                return;
            }
        }

        $animal->setStatus(AnimalStatus::AVAILABLE);
    }
}
