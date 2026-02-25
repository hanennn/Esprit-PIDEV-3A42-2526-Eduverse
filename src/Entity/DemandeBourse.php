<?php

namespace App\Entity;

use App\Repository\DemandeBourseRepository;
<<<<<<< HEAD
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
=======
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
>>>>>>> ee09f695887cdbc96e92b8b02f40161029db34ed

#[ORM\Entity(repositoryClass: DemandeBourseRepository::class)]
class DemandeBourse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

<<<<<<< HEAD
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

=======
    #[ORM\Column]
    private ?\DateTime $dateDemande = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $situationFinanciere = null;

    #[ORM\Column(length: 255)]
    private ?string $statut = null;

    #[ORM\ManyToOne(inversedBy: 'demandeBourses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'demandeBourses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Bourse $bourse = null;

>>>>>>> ee09f695887cdbc96e92b8b02f40161029db34ed
    public function getId(): ?int
    {
        return $this->id;
    }

<<<<<<< HEAD
    public function getDateDemande(): ?\DateTimeInterface
=======
    public function getDateDemande(): ?\DateTime
>>>>>>> ee09f695887cdbc96e92b8b02f40161029db34ed
    {
        return $this->dateDemande;
    }

<<<<<<< HEAD
    public function setDateDemande(\DateTimeInterface $dateDemande): static
=======
    public function setDateDemande(\DateTime $dateDemande): static
>>>>>>> ee09f695887cdbc96e92b8b02f40161029db34ed
    {
        $this->dateDemande = $dateDemande;

        return $this;
    }

<<<<<<< HEAD
    public function getNiveauEtudes(): ?string
    {
        return $this->niveauEtudes;
    }

    public function setNiveauEtudes(string $niveauEtudes): static
    {
        $this->niveauEtudes = $niveauEtudes;
=======
    public function getSituationFinanciere(): ?string
    {
        return $this->situationFinanciere;
    }

    public function setSituationFinanciere(string $situationFinanciere): static
    {
        $this->situationFinanciere = $situationFinanciere;
>>>>>>> ee09f695887cdbc96e92b8b02f40161029db34ed

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

<<<<<<< HEAD
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
=======
    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
>>>>>>> ee09f695887cdbc96e92b8b02f40161029db34ed

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
<<<<<<< HEAD

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): static
    {
        $this->note = $note;

        return $this;
    }
=======
>>>>>>> ee09f695887cdbc96e92b8b02f40161029db34ed
}
