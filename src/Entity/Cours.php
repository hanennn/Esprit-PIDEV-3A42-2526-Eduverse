<?php

namespace App\Entity;

use App\Repository\CoursRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CoursRepository::class)]
class Cours
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le titre du cours est obligatoire.")]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Le titre du cours doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le titre du cours ne peut pas dépasser {{ limit }} caractères." )]
    private ?string $titre_cours = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "La description du cours est obligatoire.")]
    #[Assert\Length(
        min: 10,
        minMessage: "La description doit contenir au moins {{ limit }} caractères."
    )]
    private ?string $desc_cours = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le niveau du cours est obligatoire.")]
    private ?string $niv_cours = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La matière du cours est obligatoire.")]
    private ?string $matiere_cours = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La langue du cours est obligatoire.")]
    private ?string $langue_cours = null;

    /**
     * @var Collection<int, Chapitres>
     */
    #[ORM\OneToMany(targetEntity: Chapitres::class, mappedBy: 'cours', orphanRemoval: true)]
    private Collection $chapitres;

    #[ORM\Column]
    private ?int $idUser = null;

    public function __construct()
    {
        $this->chapitres = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitreCours(): ?string
    {
        return $this->titre_cours;
    }

    public function setTitreCours(string $titre_cours): static
    {
        $this->titre_cours = $titre_cours;

        return $this;
    }

    public function getDescCours(): ?string
    {
        return $this->desc_cours;
    }

    public function setDescCours(string $desc_cours): static
    {
        $this->desc_cours = $desc_cours;

        return $this;
    }

    public function getNivCours(): ?string
    {
        return $this->niv_cours;
    }

    public function setNivCours(string $niv_cours): static
    {
        $this->niv_cours = $niv_cours;

        return $this;
    }

    public function getMatiereCours(): ?string
    {
        return $this->matiere_cours;
    }

    public function setMatiereCours(string $matiere_cours): static
    {
        $this->matiere_cours = $matiere_cours;

        return $this;
    }

    public function getLangueCours(): ?string
    {
        return $this->langue_cours;
    }

    public function setLangueCours(string $langue_cours): static
    {
        $this->langue_cours = $langue_cours;

        return $this;
    }

    /**
     * @return Collection<int, Chapitres>
     */
    public function getChapitres(): Collection
    {
        return $this->chapitres;
    }

    public function addChapitre(Chapitres $chapitre): static
    {
        if (!$this->chapitres->contains($chapitre)) {
            $this->chapitres->add($chapitre);
            $chapitre->setCours($this);
        }

        return $this;
    }

    public function removeChapitre(Chapitres $chapitre): static
    {
        if ($this->chapitres->removeElement($chapitre)) {
            // set the owning side to null (unless already changed)
            if ($chapitre->getCours() === $this) {
                $chapitre->setCours(null);
            }
        }

        return $this;
    }

    public function getIdUser(): ?int
    {
        return $this->idUser;
    }

    public function setIdUser(int $idUser): static
    {
        $this->idUser = $idUser;

        return $this;
    }
}
