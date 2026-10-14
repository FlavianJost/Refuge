<?php

namespace App\Entity;

use App\Repository\ReviewRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReviewRepository::class)]
class Review
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'reviews')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'Veuillez attribuer une note.')]
    #[Assert\Range(
        min: 1,
        max: 5,
        notInRangeMessage: 'La note doit être comprise entre {{ min }} et {{ max }} étoiles.',
    )]
    private ?int $rating = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'Veuillez écrire votre avis.')]
    #[Assert\Length(
        min: 3,
        max: 1000,
        minMessage: 'Votre avis est trop court.',
        maxMessage: 'Votre avis est trop long ({{ limit }} caractères maximum).',
    )]
    private ?string $comment = null;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(?int $rating): static
    {
        $this->rating = $rating;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getRatingStars(): string
    {
        $rating = $this->rating ?? 0;

        return str_repeat('★', $rating).str_repeat('☆', max(0, 5 - $rating));
    }
}
