<?php

namespace App\Entity;

use App\Repository\ContactMessageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ContactMessageRepository::class)]
class ContactMessage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'contactMessages')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank(message: 'Veuillez indiquer un sujet.')]
    #[Assert\Length(max: 150)]
    private ?string $subject = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'Veuillez écrire votre message.')]
    #[Assert\Length(
        min: 10,
        max: 2000,
        minMessage: 'Votre message est trop court.',
        maxMessage: 'Votre message est trop long ({{ limit }} caractères maximum).',
    )]
    private ?string $message = null;

    #[ORM\Column]
    private bool $handled = false;

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

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): static
    {
        $this->subject = $subject;

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

    public function isHandled(): bool
    {
        return $this->handled;
    }

    public function setHandled(bool $handled): static
    {
        $this->handled = $handled;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
