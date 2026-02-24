<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class Question
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Quiz::class, inversedBy: 'questions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Quiz $quiz = null;

    #[ORM\Column(length: 500)]
    #[Assert\NotBlank(message:"La question est obligatoire")]
    private string $question;

    #[ORM\Column]
    #[Assert\Positive(message:"Les points doivent être positifs")]
    private int $points;

    #[ORM\Column(type:'json')]
    #[Assert\Count(min:1, minMessage:"Ajoutez au moins une réponse")]
    private array $reponses = [];

   
    public function getId(): ?int { return $this->id; }
    public function getQuiz(): ?Quiz { return $this->quiz; }
    public function setQuiz(?Quiz $quiz): static { $this->quiz = $quiz; return $this; }
    public function getQuestion(): string { return $this->question; }
    public function setQuestion(string $q): static { $this->question = $q; return $this; }
    public function getPoints(): int { return $this->points; }
    public function setPoints(int $p): static { $this->points = $p; return $this; }
    public function getReponses(): array { return $this->reponses; }
    public function setReponses(array $r): static { $this->reponses = $r; return $this; }
}

