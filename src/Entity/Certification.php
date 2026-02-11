<?php

namespace App\Entity;

use App\Repository\CertificationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CertificationRepository::class)]
class Certification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\PositiveOrZero(message:"Le score doit être positif ou nul.")]
    private ?float $scoreObtenu = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message:"Le statut est obligatoire.")]
    #[Assert\Choice(choices:["Réussi","Échoué"], message:"Le statut doit être Réussi ou Échoué.")]
    private ?string $statut = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message:"Le badge est obligatoire.")]
    #[Assert\Choice(choices:["Bronze","Argent","Or","Platine"], message:"Badge invalide.")]
    private ?string $badge = null;

    #[ORM\Column]
    #[Assert\NotNull(message:"La date d'attribution est obligatoire.")]
    private ?\DateTime $dateAttribution = null;

    #[ORM\ManyToOne(inversedBy: 'certifications')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'certifications')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Quiz $quiz = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getScoreObtenu(): ?float
    {
        return $this->scoreObtenu;
    }

    public function setScoreObtenu(float $scoreObtenu): static
    {
        $this->scoreObtenu = $scoreObtenu;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getBadge(): ?string
    {
        return $this->badge;
    }

    public function setBadge(string $badge): static
    {
        $this->badge = $badge;

        return $this;
    }

    public function getDateAttribution(): ?\DateTime
    {
        return $this->dateAttribution;
    }

    public function setDateAttribution(\DateTime $dateAttribution): static
    {
        $this->dateAttribution = $dateAttribution;

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

    public function getQuiz(): ?Quiz
    {
        return $this->quiz;
    }

    public function setQuiz(?Quiz $quiz): static
    {
        $this->quiz = $quiz;

        return $this;
    }
}
