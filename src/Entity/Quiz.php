<?php
<<<<<<< HEAD
=======

>>>>>>> ee09f695887cdbc96e92b8b02f40161029db34ed
namespace App\Entity;
use App\Entity\Certification;
use App\Repository\QuizRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: QuizRepository::class)]
class Quiz
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le titre est obligatoire.")]
    private ?string $titre = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le type est obligatoire.")]
    #[Assert\Choice(choices:["Intermédiaire","Final"], message:"Type invalide.")]
    private ?string $typeQuiz = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "La durée est obligatoire.")]
    #[Assert\Positive(message: "La durée doit être positive.")]
    private ?int $duree = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Le score minimum est obligatoire.")]
    #[Assert\PositiveOrZero(message: "Le score minimum doit être positif ou nul.")]
    private ?float $scoreMinimum = null;

<<<<<<< HEAD
   
    #[ORM\ManyToOne(inversedBy: 'quizzes')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Le cours associé est obligatoire.")]
    private ?Cours $coursAssocie = null;

    
    #[ORM\OneToMany(mappedBy: 'quiz', targetEntity: Question::class, cascade:["persist"], orphanRemoval:true)]
    #[Assert\Valid]  
    private Collection $questions;
   
    #[ORM\OneToMany(mappedBy: 'quiz', targetEntity: Certification::class, orphanRemoval: true)]
    private Collection $certifications;
     // ✅ Certifications quiz final (admin)
    #[ORM\OneToMany(mappedBy: 'quiz', targetEntity: CertificationFinale::class, orphanRemoval: true)]
    private Collection $certificationsFinales;

=======
    // Relation avec Course
    #[ORM\ManyToOne(inversedBy: 'quizzes')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Le cours associé est obligatoire.")]
    private ?Course $coursAssocie = null;

    // Relation avec Question
    #[ORM\OneToMany(mappedBy: 'quiz', targetEntity: Question::class, cascade:["persist"], orphanRemoval:true)]
    #[Assert\Valid]  
    private Collection $questions;
    // Relation avec Certification
    #[ORM\OneToMany(mappedBy: 'quiz', targetEntity: Certification::class, orphanRemoval: true)]
    private Collection $certifications;
>>>>>>> ee09f695887cdbc96e92b8b02f40161029db34ed

    public function __construct()
    {
        $this->questions = new ArrayCollection();
        $this->certifications = new ArrayCollection();
<<<<<<< HEAD
         $this->certificationsFinales = new ArrayCollection();

    }

   
=======
    }

    // -------------------------
    // Getters / Setters
    // -------------------------
>>>>>>> ee09f695887cdbc96e92b8b02f40161029db34ed
    public function getId(): ?int { return $this->id; }

    public function getTitre(): ?string { return $this->titre; }
    public function setTitre(string $titre): static { $this->titre = $titre; return $this; }

    public function getTypeQuiz(): ?string { return $this->typeQuiz; }
    public function setTypeQuiz(string $typeQuiz): static { $this->typeQuiz = $typeQuiz; return $this; }

    public function getDuree(): ?int { return $this->duree; }
    public function setDuree(int $duree): static { $this->duree = $duree; return $this; }

    public function getScoreMinimum(): ?float { return $this->scoreMinimum; }
    public function setScoreMinimum(float $scoreMinimum): static { $this->scoreMinimum = $scoreMinimum; return $this; }

<<<<<<< HEAD
    public function getCoursAssocie(): ?Cours { return $this->coursAssocie; }
    public function setCoursAssocie(?Cours $coursAssocie): static { $this->coursAssocie = $coursAssocie; return $this; }
=======
    public function getCoursAssocie(): ?Course { return $this->coursAssocie; }
    public function setCoursAssocie(?Course $coursAssocie): static { $this->coursAssocie = $coursAssocie; return $this; }
>>>>>>> ee09f695887cdbc96e92b8b02f40161029db34ed
    /**
 * @return Collection<int, Certification>
 */
public function getCertifications(): Collection
{
    return $this->certifications;
}

public function addCertification(Certification $certification): static
{
    if (!$this->certifications->contains($certification)) {
        $this->certifications->add($certification);
        $certification->setQuiz($this);
    }

    return $this;
}

public function removeCertification(Certification $certification): static
{
    if ($this->certifications->removeElement($certification)) {
        if ($certification->getQuiz() === $this) {
            $certification->setQuiz(null);
        }
    }

    return $this;
}

    /**
     * @return Collection<int, Question>
     */
    public function getQuestions(): Collection { return $this->questions; }

    public function addQuestion(Question $q): static
    {
        if (!$this->questions->contains($q)) {
            $this->questions->add($q);
            $q->setQuiz($this);
        }
        return $this;
    }

    public function removeQuestion(Question $q): static
    {
        if ($this->questions->removeElement($q)) {
            if ($q->getQuiz() === $this) { $q->setQuiz(null); }
        }
        return $this;
    }
<<<<<<< HEAD
      /**
     * @return Collection<int, CertificationFinale>
     */
    public function getCertificationsFinales(): Collection
    {
        return $this->certificationsFinales;
    }

    public function addCertificationFinale(CertificationFinale $certificationFinale): static
    {
        if (!$this->certificationsFinales->contains($certificationFinale)) {
            $this->certificationsFinales->add($certificationFinale);
            $certificationFinale->setQuiz($this);
        }
        return $this;
    }

    public function removeCertificationFinale(CertificationFinale $certificationFinale): static
    {
        if ($this->certificationsFinales->removeElement($certificationFinale)) {
            if ($certificationFinale->getQuiz() === $this) {
                $certificationFinale->setQuiz(null);
            }
        }
        return $this;
    }

}

=======
}
>>>>>>> ee09f695887cdbc96e92b8b02f40161029db34ed
