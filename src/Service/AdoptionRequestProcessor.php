<?php

namespace App\Service;

use App\Entity\AdoptionRequest;
use App\Entity\AdoptionRequestEvent;
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

    public function changeStatus(AdoptionRequest $request, AdoptionRequestStatus $status, User $volunteer, ?string $note = null): void
    {
        match ($status) {
            AdoptionRequestStatus::ACCEPTED => $this->accept($request, $volunteer),
            AdoptionRequestStatus::REJECTED => $this->reject($request, $volunteer),
            default => $this->reopen($request, $status),
        };

        $this->recordEvent($request, $status, $volunteer, $note);

        $this->entityManager->flush();
    }

    private function accept(AdoptionRequest $request, User $volunteer): void
    {
        $request->setStatus(AdoptionRequestStatus::ACCEPTED)
            ->setProcessedBy($volunteer)
            ->touch();

        $animal = $request->getAnimal();

        if (null === $animal) {
            return;
        }

        $animal->setStatus(AnimalStatus::ADOPTED);

        foreach ($animal->getAdoptionRequests() as $other) {
            if ($other !== $request && AdoptionRequestStatus::REJECTED !== $other->getStatus()) {
                $other->setStatus(AdoptionRequestStatus::REJECTED)
                    ->setProcessedBy($volunteer)
                    ->touch();

                $this->recordEvent(
                    $other,
                    AdoptionRequestStatus::REJECTED,
                    $volunteer,
                    'Demande clôturée automatiquement : l\'animal a été adopté via une autre demande.',
                );
            }
        }
    }

    private function reject(AdoptionRequest $request, User $volunteer): void
    {
        $request->setStatus(AdoptionRequestStatus::REJECTED)
            ->setProcessedBy($volunteer)
            ->touch();
    }

    private function reopen(AdoptionRequest $request, AdoptionRequestStatus $status): void
    {
        $request->setStatus($status)
            ->setProcessedBy(null)
            ->touch();

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

    private function recordEvent(AdoptionRequest $request, AdoptionRequestStatus $status, ?User $author, ?string $note = null): void
    {
        $request->addEvent(
            (new AdoptionRequestEvent())
                ->setStatus($status)
                ->setAuthor($author)
                ->setNote($note),
        );
    }
}
