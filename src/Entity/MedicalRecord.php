<?php

namespace App\Entity;

use App\Enum\MedicalRecordCategory;
use App\Repository\MedicalRecordRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MedicalRecordRepository::class)]
class MedicalRecord
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'medicalRecords')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Animal $animal = null;

    #[ORM\Column(type: 'date_immutable')]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(length: 30, enumType: MedicalRecordCategory::class)]
    private MedicalRecordCategory $category = MedicalRecordCategory::OTHER;

    #[ORM\Column(length: 160)]
    #[Assert\NotBlank(message: 'Le motif est obligatoire.')]
    #[Assert\Length(max: 160)]
    private ?string $title = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 160, nullable: true)]
    private ?string $veterinarian = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->date = new \DateTimeImmutable('today');
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

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getCategory(): MedicalRecordCategory
    {
        return $this->category;
    }

    public function setCategory(MedicalRecordCategory $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

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

    public function getVeterinarian(): ?string
    {
        return $this->veterinarian;
    }

    public function setVeterinarian(?string $veterinarian): static
    {
        $this->veterinarian = $veterinarian;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
