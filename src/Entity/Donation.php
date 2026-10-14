<?php

namespace App\Entity;

use App\Enum\DonationStatus;
use App\Repository\DonationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DonationRepository::class)]
class Donation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Montant du don exprimé en centimes d'euro.
     */
    #[ORM\Column]
    #[Assert\NotBlank(message: 'Veuillez indiquer un montant.')]
    #[Assert\Positive(message: 'Le montant du don doit être supérieur à zéro.')]
    #[Assert\GreaterThanOrEqual(value: 100, message: 'Le montant minimum est de 1 €.')]
    private ?int $amount = null;

    #[ORM\Column(length: 120, nullable: true)]
    #[Assert\Length(max: 120)]
    private ?string $donorName = null;

    #[ORM\Column(length: 180, nullable: true)]
    #[Assert\Email(message: 'Veuillez saisir une adresse email valide.')]
    private ?string $donorEmail = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\Length(max: 2000)]
    private ?string $message = null;

    #[ORM\Column(length: 30, enumType: DonationStatus::class)]
    private DonationStatus $status = DonationStatus::PAID;

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

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(?int $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getAmountInEuros(): float
    {
        return ($this->amount ?? 0) / 100;
    }

    public function getDonorName(): ?string
    {
        return $this->donorName;
    }

    public function setDonorName(?string $donorName): static
    {
        $this->donorName = $donorName;

        return $this;
    }

    public function getDonorEmail(): ?string
    {
        return $this->donorEmail;
    }

    public function setDonorEmail(?string $donorEmail): static
    {
        $this->donorEmail = $donorEmail;

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

    public function getStatus(): DonationStatus
    {
        return $this->status;
    }

    public function setStatus(DonationStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function __toString(): string
    {
        return number_format($this->getAmountInEuros(), 2, ',', ' ').' €';
    }
}
