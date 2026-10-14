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
#[ORM\UniqueConstraint(name: 'UNIQ_ANIMAL_SLUG', columns: ['slug'])]
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

    #[ORM\Column(length: 160)]
    private ?string $slug = null;

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

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $views = 0;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, AdoptionRequest>
     */
    #[ORM\OneToMany(targetEntity: AdoptionRequest::class, mappedBy: 'animal', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $adoptionRequests;

    /**
     * @var Collection<int, AnimalPhoto>
     */
    #[ORM\OneToMany(targetEntity: AnimalPhoto::class, mappedBy: 'animal', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'ASC', 'id' => 'ASC'])]
    private Collection $photos;

    /**
     * @var Collection<int, MedicalRecord>
     */
    #[ORM\OneToMany(targetEntity: MedicalRecord::class, mappedBy: 'animal', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['date' => 'DESC', 'id' => 'DESC'])]
    private Collection $medicalRecords;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->adoptionRequests = new ArrayCollection();
        $this->photos = new ArrayCollection();
        $this->medicalRecords = new ArrayCollection();
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

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

    public function getViews(): int
    {
        return $this->views;
    }

    public function incrementViews(): static
    {
        ++$this->views;

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

    /**
     * @return Collection<int, AnimalPhoto>
     */
    public function getPhotos(): Collection
    {
        return $this->photos;
    }

    public function addPhoto(AnimalPhoto $photo): static
    {
        if (!$this->photos->contains($photo)) {
            $this->photos->add($photo);
            $photo->setAnimal($this);
        }

        return $this;
    }

    public function removePhoto(AnimalPhoto $photo): static
    {
        if ($this->photos->removeElement($photo) && $photo->getAnimal() === $this) {
            $photo->setAnimal(null);
        }

        return $this;
    }

    /**
     * @return Collection<int, MedicalRecord>
     */
    public function getMedicalRecords(): Collection
    {
        return $this->medicalRecords;
    }

    public function addMedicalRecord(MedicalRecord $record): static
    {
        if (!$this->medicalRecords->contains($record)) {
            $this->medicalRecords->add($record);
            $record->setAnimal($this);
        }

        return $this;
    }

    public function removeMedicalRecord(MedicalRecord $record): static
    {
        if ($this->medicalRecords->removeElement($record) && $record->getAnimal() === $this) {
            $record->setAnimal(null);
        }

        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }
}
