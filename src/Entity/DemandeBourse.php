<?php

namespace App\Entity;

use App\Repository\DemandeBourseRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DemandeBourseRepository::class)]
class DemandeBourse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateDemande = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le niveau d'études est obligatoire.")]
    #[Assert\Choice(
        choices: ['Licence', 'Master', 'Doctorat', 'Ingénierie', 'Autre'],
        message: "Le niveau d'études sélectionné n'est pas valide."
    )]
    private ?string $niveauEtudes = null;

    #[ORM\Column(length: 255)]
    private ?string $statut = 'En attente';

    #[ORM\Column(length: 255)]
    private ?string $lettreMotivation = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $note = null;

    #[ORM\ManyToOne(inversedBy: 'demandesBourse')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $etudiant = null;

    #[ORM\ManyToOne(inversedBy: 'demandes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Bourse $bourse = null;

    public function __construct()
    {
        $this->dateDemande = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateDemande(): ?\DateTimeInterface
    {
        return $this->dateDemande;
    }

    public function setDateDemande(\DateTimeInterface $dateDemande): static
    {
        $this->dateDemande = $dateDemande;

        return $this;
    }

    public function getNiveauEtudes(): ?string
    {
        return $this->niveauEtudes;
    }

    public function setNiveauEtudes(string $niveauEtudes): static
    {
        $this->niveauEtudes = $niveauEtudes;

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

    public function getLettreMotivation(): ?string
    {
        return $this->lettreMotivation;
    }

    public function setLettreMotivation(string $lettreMotivation): static
    {
        $this->lettreMotivation = $lettreMotivation;

        return $this;
    }

    public function getEtudiant(): ?User
    {
        return $this->etudiant;
    }

    public function setEtudiant(?User $etudiant): static
    {
        $this->etudiant = $etudiant;

        return $this;
    }

    public function getBourse(): ?Bourse
    {
        return $this->bourse;
    }

    public function setBourse(?Bourse $bourse): static
    {
        $this->bourse = $bourse;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): static
    {
        $this->note = $note;

        return $this;
    }
}
