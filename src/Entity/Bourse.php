<?php

namespace App\Entity;

use App\Repository\BourseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BourseRepository::class)]
class Bourse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(float $montant): static
    {
        $this->montant = $montant;

        return $this;
    }

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
    }

    /**
     * @return Collection<int, DemandeBourse>
     */
    public function getDemandeBourses(): Collection
    {
        return $this->demandeBourses;
    }

    public function addDemandeBourse(DemandeBourse $demandeBourse): static
    {
        if (!$this->demandeBourses->contains($demandeBourse)) {
            $this->demandeBourses->add($demandeBourse);
            $demandeBourse->setBourse($this);
        }

        return $this;
    }

    public function removeDemandeBourse(DemandeBourse $demandeBourse): static
    {
        if ($this->demandeBourses->removeElement($demandeBourse)) {
            // set the owning side to null (unless already changed)
            if ($demandeBourse->getBourse() === $this) {
                $demandeBourse->setBourse(null);
            }
        }

        return $this;
    }
}
