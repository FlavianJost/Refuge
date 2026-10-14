<?php

namespace App\Entity;

use App\Enum\AnimalStatus;
use App\Enum\Species;
use App\Repository\AnimalRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AnimalRepository::class)]
class Animal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    #[Assert\Length(max: 120)]
    private ?string $name = null;

    #[ORM\Column(length: 30, enumType: Species::class)]
    private Species $species = Species::OTHER;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $breed = null;

    #[ORM\Column(type: 'smallint', nullable: true)]
    #[Assert\PositiveOrZero(message: 'L\'âge doit être positif ou nul.')]
    private ?int $age = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 30, enumType: AnimalStatus::class)]
    private AnimalStatus $status = AnimalStatus::AVAILABLE;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, AdoptionRequest>
     */
    #[ORM\OneToMany(targetEntity: AdoptionRequest::class, mappedBy: 'animal', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $adoptionRequests;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->adoptionRequests = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSpecies(): Species
    {
        return $this->species;
    }

    public function setSpecies(Species $species): static
    {
        $this->species = $species;

        return $this;
    }

    public function getBreed(): ?string
    {
        return $this->breed;
    }

    public function setBreed(?string $breed): static
    {
        $this->breed = $breed;

        return $this;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(?int $age): static
    {
        $this->age = $age;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStatus(): AnimalStatus
    {
        return $this->status;
    }

    public function setStatus(AnimalStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;

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

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function touch(): static
    {
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function isAvailable(): bool
    {
        return AnimalStatus::AVAILABLE === $this->status;
    }

    /**
     * @return Collection<int, AdoptionRequest>
     */
    public function getAdoptionRequests(): Collection
    {
        return $this->adoptionRequests;
    }

    public function addAdoptionRequest(AdoptionRequest $request): static
    {
        if (!$this->adoptionRequests->contains($request)) {
            $this->adoptionRequests->add($request);
            $request->setAnimal($this);
        }

        return $this;
    }

    public function removeAdoptionRequest(AdoptionRequest $request): static
    {
        if ($this->adoptionRequests->removeElement($request) && $request->getAnimal() === $this) {
            $request->setAnimal(null);
        }

        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }
}
