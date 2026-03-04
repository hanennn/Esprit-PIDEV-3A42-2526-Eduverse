<?php

namespace App\Entity;

use App\Repository\CoursRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CoursRepository::class)]
class Cours
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(message: "Le titre du cours est obligatoire.")]
    private ?string $titre_cours = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\NotBlank(message: "Le niveau du cours est obligatoire.")]
    private ?string $niv_cours = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\NotBlank(message: "La matière du cours est obligatoire.")]
    private ?string $matiere_cours = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\NotBlank(message: "La langue du cours est obligatoire.")]
    private ?string $langue_cours = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'coursCreated')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull(message: "Le créateur du cours est obligatoire.")]
    private ?User $createur = null;


    /** @var Collection<int, Quiz> */
    #[ORM\OneToMany(mappedBy: 'coursAssocie', targetEntity: Quiz::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $quizzes;

    /**
     * @var Collection<int, Chapitres>
     */
    #[ORM\OneToMany(mappedBy: 'cours', targetEntity: Chapitres::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $chapitres;

    public function __construct()
    {
        $this->quizzes = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getCreateur(): ?User
    {
        return $this->createur;
    }

    public function setCreateur(?User $createur): static
    {
        $this->createur = $createur;
        return $this;
    }

    /**
     * @return Collection<int, Quiz>
     */
    public function getQuizzes(): Collection
    {
        return $this->quizzes;
    }

    public function addQuiz(Quiz $quiz): static
    {
        if (!$this->quizzes->contains($quiz)) {
            $this->quizzes->add($quiz);
            $quiz->setCoursAssocie($this);
        }
        return $this;
    }

    public function removeQuiz(Quiz $quiz): static
    {
        if ($this->quizzes->removeElement($quiz)) {
            if ($quiz->getCoursAssocie() === $this) {
                $quiz->setCoursAssocie(null);
            }
        }
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
            if ($chapitre->getCours() === $this) {
                $chapitre->setCours(null);
            }
        }
        return $this;
    }
}