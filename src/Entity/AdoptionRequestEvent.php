<?php

namespace App\Entity;

use App\Enum\AdoptionRequestStatus;
use App\Repository\AdoptionRequestEventRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AdoptionRequestEventRepository::class)]
class AdoptionRequestEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?AdoptionRequest $adoptionRequest = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?User $author = null;

    #[ORM\Column(length: 30, enumType: AdoptionRequestStatus::class)]
    private AdoptionRequestStatus $status = AdoptionRequestStatus::PENDING;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $note = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

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

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getStatus(): AdoptionRequestStatus
    {
        return $this->status;
    }

    public function setStatus(AdoptionRequestStatus $status): static
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

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function title(): string
    {
        return match ($this->status) {
            AdoptionRequestStatus::PENDING => 'Demande déposée',
            AdoptionRequestStatus::IN_PROGRESS => 'Demande prise en charge',
            AdoptionRequestStatus::ACCEPTED => 'Demande acceptée',
            AdoptionRequestStatus::REJECTED => 'Demande refusée',
        };
    }
}
