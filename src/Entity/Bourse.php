<?php

namespace App\Entity;

use App\Repository\BourseRepository;
<<<<<<< HEAD
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
=======
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
>>>>>>> ee09f695887cdbc96e92b8b02f40161029db34ed

#[ORM\Entity(repositoryClass: BourseRepository::class)]
class Bourse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

<<<<<<< HEAD
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le titre est obligatoire.")]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "La description est obligatoire.")]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank(message: "La date d'attribution est obligatoire.")]
    #[Assert\GreaterThan("today", message: "La date doit être future.")]
    private ?\DateTimeInterface $dateAttribution = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank(message: "La date de fin est obligatoire.")]
    #[Assert\GreaterThan(propertyPath: "dateAttribution", message: "La date de fin doit être après la date de début.")]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Le montant est obligatoire.")]
    #[Assert\Positive(message: "Le montant doit être supérieur à 0.")]
    private ?float $montant = null;
=======
    #[ORM\Column]
    private ?float $montant = null;

    #[ORM\Column]
    private ?\DateTime $dateAttribution = null;

    #[ORM\Column]
    private ?\DateTime $dateFin = null;

    /**
     * @var Collection<int, DemandeBourse>
     */
    #[ORM\OneToMany(targetEntity: DemandeBourse::class, mappedBy: 'bourse', orphanRemoval: true)]
    private Collection $demandeBourses;

    public function __construct()
    {
        $this->demandeBourses = new ArrayCollection();
    }
>>>>>>> ee09f695887cdbc96e92b8b02f40161029db34ed

    public function getId(): ?int
    {
        return $this->id;
    }

<<<<<<< HEAD
    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getDateAttribution(): ?\DateTimeInterface
    {
        return $this->dateAttribution;
    }

    public function setDateAttribution(\DateTimeInterface $dateAttribution): static
    {
        $this->dateAttribution = $dateAttribution;

        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTimeInterface $dateFin): static
    {
        $this->dateFin = $dateFin;

        return $this;
    }

=======
>>>>>>> ee09f695887cdbc96e92b8b02f40161029db34ed
    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(float $montant): static
    {
        $this->montant = $montant;

        return $this;
    }

<<<<<<< HEAD
    #[ORM\OneToMany(mappedBy: 'bourse', targetEntity: DemandeBourse::class, orphanRemoval: true)]
    private Collection $demandes;

    public function __construct()
    {
        $this->demandes = new \Doctrine\Common\Collections\ArrayCollection();
=======
    public function getDateAttribution(): ?\DateTime
    {
        return $this->dateAttribution;
    }

    public function setDateAttribution(\DateTime $dateAttribution): static
    {
        $this->dateAttribution = $dateAttribution;

        return $this;
    }

    public function getDateFin(): ?\DateTime
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTime $dateFin): static
    {
        $this->dateFin = $dateFin;

        return $this;
>>>>>>> ee09f695887cdbc96e92b8b02f40161029db34ed
    }

    /**
     * @return Collection<int, DemandeBourse>
     */
<<<<<<< HEAD
    public function getDemandes(): Collection
    {
        return $this->demandes;
    }

    public function addDemande(DemandeBourse $demande): static
    {
        if (!$this->demandes->contains($demande)) {
            $this->demandes->add($demande);
            $demande->setBourse($this);
=======
    public function getDemandeBourses(): Collection
    {
        return $this->demandeBourses;
    }

    public function addDemandeBourse(DemandeBourse $demandeBourse): static
    {
        if (!$this->demandeBourses->contains($demandeBourse)) {
            $this->demandeBourses->add($demandeBourse);
            $demandeBourse->setBourse($this);
>>>>>>> ee09f695887cdbc96e92b8b02f40161029db34ed
        }

        return $this;
    }

<<<<<<< HEAD
    public function removeDemande(DemandeBourse $demande): static
    {
        if ($this->demandes->removeElement($demande)) {
            // set the owning side to null (unless already changed)
            if ($demande->getBourse() === $this) {
                $demande->setBourse(null);
=======
    public function removeDemandeBourse(DemandeBourse $demandeBourse): static
    {
        if ($this->demandeBourses->removeElement($demandeBourse)) {
            // set the owning side to null (unless already changed)
            if ($demandeBourse->getBourse() === $this) {
                $demandeBourse->setBourse(null);
>>>>>>> ee09f695887cdbc96e92b8b02f40161029db34ed
            }
        }

        return $this;
    }
}
