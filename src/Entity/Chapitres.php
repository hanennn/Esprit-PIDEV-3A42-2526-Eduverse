<?php

namespace App\Entity;

use App\Repository\ChapitresRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ChapitresRepository::class)]
class Chapitres
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(message: "Le titre du chapitre est obligatoire.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Le titre du chapitre ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $titre_chap = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
     #[Assert\NotBlank(message: "La description du chapitre est obligatoire.")]
    #[Assert\Length(
        min: 10,
        minMessage: "La description doit contenir au moins {{ limit }} caractères."
    )]
    private ?string $desc_chap = null;

    #[ORM\Column(nullable: true)]
    #[Assert\NotBlank(message: "L'ordre du chapitre est obligatoire.")]
    #[Assert\Positive(message: "L'ordre du chapitre doit être un nombre positif.")]
    private ?int $ordre_chap = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(message: "La durée du chapitre est obligatoire.")]
    private ?string $duree_chap = null;

    #[ORM\Column(length: 20, nullable: true)]
     #[Assert\NotBlank(message: "Le statut du chapitre est obligatoire.")]
     #[Assert\Choice(
        choices: ['Ouvert', 'Non ouvert'],
        message: "Le statut doit être 'Ouvert' ou 'Non ouvert'."
    )]

    private ?string $statutChap = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $resume_chap = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(message: "Le contenu du chapitre est obligatoire.", groups: ['file_upload'])]
    private ?string $contenu_chap = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(message: "Le type de contenu est obligatoire.")]
    #[Assert\Choice(
        choices: ['pdf', 'vidéo'],
        message: "Le type de contenu doit être 'pdf', 'vidéo'"
    )]
    private ?string $typeContenu = null;

    #[ORM\ManyToOne(inversedBy: 'chapitres')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Cours $cours = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitreChap(): ?string
    {
        return $this->titre_chap;
    }

    public function setTitreChap(string $titre_chap): static
    {
        $this->titre_chap = $titre_chap;

        return $this;
    }

    public function getDescChap(): ?string
    {
        return $this->desc_chap;
    }

    public function setDescChap(string $desc_chap): static
    {
        $this->desc_chap = $desc_chap;

        return $this;
    }

    public function getOrdreChap(): ?int
    {
        return $this->ordre_chap;
    }

    public function setOrdreChap(int $ordre_chap): static
    {
        $this->ordre_chap = $ordre_chap;

        return $this;
    }

    public function getDureeChap(): ?string
    {
        return $this->duree_chap;
    }

    public function setDureeChap(string $duree_chap): static
    {
        $this->duree_chap = $duree_chap;

        return $this;
    }

    public function getStatutChap(): ?string
    {
        return $this->statutChap;
    }

    public function setStatutChap(string $statutChap): static
    {
        $this->statutChap = $statutChap;

        return $this;
    }

    public function getResumeChap(): ?string
    {
        return $this->resume_chap;
    }

    public function setResumeChap(?string $resume_chap): static
    {
        $this->resume_chap = $resume_chap;

        return $this;
    }

    public function getContenuChap(): ?string
    {
        return $this->contenu_chap;
    }

    public function setContenuChap(string $contenu_chap): static
    {
        $this->contenu_chap = $contenu_chap;

        return $this;
    }

    public function getTypeContenu(): ?string
    {
        return $this->typeContenu;
    }

    public function setTypeContenu(string $typeContenu): static
    {
        $this->typeContenu = $typeContenu;

        return $this;
    }

    public function getCours(): ?Cours
    {
        return $this->cours;
    }

    public function setCours(?Cours $cours): static
    {
        $this->cours = $cours;

        return $this;
    }
}