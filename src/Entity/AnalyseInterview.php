<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'analyse_interview')]
class AnalyseInterview
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: DemandeBourse::class, inversedBy: 'analyseInterview')]
    #[ORM\JoinColumn(nullable: false)]
    private ?DemandeBourse $demandeBourse = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $transcription = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $scoreDetermine = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $scoreAnxieux = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $scoreConfiant = null;

    #[ORM\Column(name: 'score_motive', type: 'float', nullable: true)]
    private ?float $scoreMotive = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $scoreHesitant = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $debitParole = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $tauxHesitations = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $energieVocale = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $profilGlobal = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $recommandation = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $cheminAudio = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateInterview = null;

    // ── GETTERS & SETTERS ──────────────────────────────────────────────────────

    public function getId(): ?int { return $this->id; }

    public function getDemandeBourse(): ?DemandeBourse { return $this->demandeBourse; }
    public function setDemandeBourse(?DemandeBourse $d): static { $this->demandeBourse = $d; return $this; }

    public function getTranscription(): ?string { return $this->transcription; }
    public function setTranscription(?string $t): static { $this->transcription = $t; return $this; }

    public function getScoreDetermine(): ?float { return $this->scoreDetermine; }
    public function setScoreDetermine(?float $s): static { $this->scoreDetermine = $s; return $this; }

    public function getScoreAnxieux(): ?float { return $this->scoreAnxieux; }
    public function setScoreAnxieux(?float $s): static { $this->scoreAnxieux = $s; return $this; }

    public function getScoreConfiant(): ?float { return $this->scoreConfiant; }
    public function setScoreConfiant(?float $s): static { $this->scoreConfiant = $s; return $this; }

    public function getScoreMotive(): ?float { return $this->scoreMotive; }
    public function setScoreMotive(?float $s): static { $this->scoreMotive = $s; return $this; }

    public function getScoreHesitant(): ?float { return $this->scoreHesitant; }
    public function setScoreHesitant(?float $s): static { $this->scoreHesitant = $s; return $this; }

    public function getDebitParole(): ?int { return $this->debitParole; }
    public function setDebitParole(?int $d): static { $this->debitParole = $d; return $this; }

    public function getTauxHesitations(): ?float { return $this->tauxHesitations; }
    public function setTauxHesitations(?float $t): static { $this->tauxHesitations = $t; return $this; }

    public function getEnergieVocale(): ?string { return $this->energieVocale; }
    public function setEnergieVocale(?string $e): static { $this->energieVocale = $e; return $this; }

    public function getProfilGlobal(): ?string { return $this->profilGlobal; }
    public function setProfilGlobal(?string $p): static { $this->profilGlobal = $p; return $this; }

    public function getRecommandation(): ?string { return $this->recommandation; }
    public function setRecommandation(?string $r): static { $this->recommandation = $r; return $this; }

    public function getCheminAudio(): ?string { return $this->cheminAudio; }
    public function setCheminAudio(?string $c): static { $this->cheminAudio = $c; return $this; }

    public function getDateInterview(): ?\DateTimeInterface { return $this->dateInterview; }
    public function setDateInterview(\DateTimeInterface $d): static { $this->dateInterview = $d; return $this; }
}
