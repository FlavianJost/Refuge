<?php

namespace App\Entity;

use App\Enum\AdoptionRequestStatus;
use App\Enum\HousingType;
use App\Repository\AdoptionRequestRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AdoptionRequestRepository::class)]
class AdoptionRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'adoptionRequests')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Animal $animal = null;

    #[ORM\ManyToOne(inversedBy: 'adoptionRequests')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'processedRequests')]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?User $processedBy = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\Length(max: 2000)]
    private ?string $message = null;

    #[ORM\Column(length: 20, enumType: HousingType::class)]
    private HousingType $housing = HousingType::OTHER;

    #[ORM\Column(type: 'boolean')]
    private bool $hasGarden = false;

    #[ORM\Column(type: 'boolean')]
    private bool $hasOtherPets = false;

    #[ORM\Column(type: 'boolean')]
    private bool $hasChildren = false;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\Length(max: 2000)]
    private ?string $experience = null;

    #[ORM\Column(length: 30, enumType: AdoptionRequestStatus::class)]
    private AdoptionRequestStatus $status = AdoptionRequestStatus::PENDING;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, AdoptionRequestEvent>
     */
    #[ORM\OneToMany(targetEntity: AdoptionRequestEvent::class, mappedBy: 'adoptionRequest', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'ASC', 'id' => 'ASC'])]
    private Collection $events;

    /**
     * @var Collection<int, VisitAppointment>
     */
    #[ORM\OneToMany(targetEntity: VisitAppointment::class, mappedBy: 'adoptionRequest', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'DESC', 'id' => 'DESC'])]
    private Collection $appointments;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->events = new ArrayCollection();
        $this->appointments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAnimal(): ?Animal
    {
        return $this->animal;
    }

    public function setAnimal(?Animal $animal): static
    {
        $this->animal = $animal;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

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

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getHousing(): HousingType
    {
        return $this->housing;
    }

    public function setHousing(HousingType $housing): static
    {
        $this->housing = $housing;

        return $this;
    }

    public function hasGarden(): bool
    {
        return $this->hasGarden;
    }

    public function setHasGarden(bool $hasGarden): static
    {
        $this->hasGarden = $hasGarden;

        return $this;
    }

    public function hasOtherPets(): bool
    {
        return $this->hasOtherPets;
    }

    public function setHasOtherPets(bool $hasOtherPets): static
    {
        $this->hasOtherPets = $hasOtherPets;

        return $this;
    }

    public function hasChildren(): bool
    {
        return $this->hasChildren;
    }

    public function setHasChildren(bool $hasChildren): static
    {
        $this->hasChildren = $hasChildren;

        return $this;
    }

    public function getExperience(): ?string
    {
        return $this->experience;
    }

    public function setExperience(?string $experience): static
    {
        $this->experience = $experience;

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

    /**
     * @return Collection<int, AdoptionRequestEvent>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(AdoptionRequestEvent $event): static
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->setAdoptionRequest($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, VisitAppointment>
     */
    public function getAppointments(): Collection
    {
        return $this->appointments;
    }

    public function addAppointment(VisitAppointment $appointment): static
    {
        if (!$this->appointments->contains($appointment)) {
            $this->appointments->add($appointment);
            $appointment->setAdoptionRequest($this);
        }

        return $this;
    }
}
