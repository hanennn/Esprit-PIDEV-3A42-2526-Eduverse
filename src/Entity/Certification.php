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

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero(message:"Le score doit être positif ou nul.")]
    private ?float $scoreObtenu = null;

    #[ORM\Column(length: 255,nullable: true)]
    #[Assert\NotBlank(message:"Le statut est obligatoire.")]
    #[Assert\Choice(choices:["Réussi","Échoué"], message:"Le statut doit être Réussi ou Échoué.")]
    private ?string $statut = null;

    #[ORM\Column(length: 255,nullable: true)]
    #[Assert\NotBlank(message:"Le badge est obligatoire.")]
    #[Assert\Choice(choices:["Bronze","Argent","Or","Platine"], message:"Badge invalide.")]
    private ?string $badge = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Assert\NotNull(message:"La date d'attribution est obligatoire.")]
    private ?\DateTimeImmutable $dateAttribution = null;

    #[ORM\ManyToOne(inversedBy: 'certifications')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'certifications')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Quiz $quiz = null;
    #[ORM\OneToOne(targetEntity: CertificationFinale::class, mappedBy: 'tentative')]
    private ?CertificationFinale $certificationFinale = null;

     public function __construct()
    {
        // ✅ important: toujours une date par défaut
        $this->dateAttribution = new \DateTimeImmutable();
    }


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
     public function getCertificationFinale(): ?CertificationFinale
{
    return $this->certificationFinale;
}

public function setCertificationFinale(?CertificationFinale $certificationFinale): static
{
    $this->certificationFinale = $certificationFinale;
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

    public function getDateAttribution(): ?\DateTimeImmutable
    {
        return $this->dateAttribution;
    }

    public function setDateAttribution(\DateTimeImmutable $dateAttribution): static
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
