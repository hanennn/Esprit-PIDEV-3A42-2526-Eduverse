<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class CertificationFinale
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    #[Assert\NotNull(message: "La date d'émission est obligatoire.")]
    private ?\DateTimeImmutable $dateEmission = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\NotBlank(message: "Le badge est obligatoire.")]
    #[Assert\Choice(
        choices: ["Bronze", "Argent", "Or", "Platine"],
        message: "Badge invalide."
    )]
    private ?string $badge = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]  
    #[Assert\NotNull(message: "L'utilisateur est obligatoire.")]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'certificationsFinales')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull(message: "Le quiz est obligatoire.")]
    private ?Quiz $quiz = null;

    #[ORM\OneToOne]
    #[ORM\JoinColumn(nullable: false, unique: true)]
    #[Assert\NotNull(message: "La tentative est obligatoire.")]
    private ?Certification $tentative = null;

    public function getId(): ?int { return $this->id; }

    public function getDateEmission(): ?\DateTimeImmutable
{
    return $this->dateEmission;
}
    public function setDateEmission(\DateTimeImmutable $dateEmission): static
{
    $this->dateEmission = $dateEmission;
    return $this;
}

    public function getBadge(): ?string { return $this->badge; }
    public function setBadge(string $badge): static
    {
        $this->badge = $badge;
        return $this;
    }

    public function getUser(): ?User { return $this->user; }
    public function setUser(User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getQuiz(): ?Quiz { return $this->quiz; }
   public function setQuiz(?Quiz $quiz): static
{
    $this->quiz = $quiz;
    return $this;
}

    public function getTentative(): ?Certification { return $this->tentative; }
    public function setTentative(Certification $tentative): static
    {
        $this->tentative = $tentative;
        return $this;
    }
}
