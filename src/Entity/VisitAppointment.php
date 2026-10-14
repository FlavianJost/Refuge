<?php

namespace App\Entity;

use App\Enum\VisitAppointmentStatus;
use App\Repository\VisitAppointmentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VisitAppointmentRepository::class)]
class VisitAppointment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'appointments')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?AdoptionRequest $adoptionRequest = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?User $processedBy = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $proposedAt = null;

    #[ORM\Column(length: 30, enumType: VisitAppointmentStatus::class)]
    private VisitAppointmentStatus $status = VisitAppointmentStatus::PENDING;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $note = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $response = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdoptionRequest(): ?AdoptionRequest
    {
        return $this->adoptionRequest;
    }

    public function setAdoptionRequest(?AdoptionRequest $adoptionRequest): static
    {
        $this->adoptionRequest = $adoptionRequest;

        return $this;
    }

    public function getProcessedBy(): ?User
    {
        return $this->processedBy;
    }

    public function setProcessedBy(?User $processedBy): static
    {
        $this->processedBy = $processedBy;

        return $this;
    }

    public function getProposedAt(): ?\DateTimeImmutable
    {
        return $this->proposedAt;
    }

    public function setProposedAt(\DateTimeImmutable $proposedAt): static
    {
        $this->proposedAt = $proposedAt;

        return $this;
    }

    public function getStatus(): VisitAppointmentStatus
    {
        return $this->status;
    }

    public function setStatus(VisitAppointmentStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): static
    {
        $this->note = $note;

        return $this;
    }

    public function getResponse(): ?string
    {
        return $this->response;
    }

    public function setResponse(?string $response): static
    {
        $this->response = $response;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function touch(): static
    {
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }
}
