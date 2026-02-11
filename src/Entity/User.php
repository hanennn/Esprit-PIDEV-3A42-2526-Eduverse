<?php
namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['Username'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: "Le numéro d'inscription (Username) est obligatoire.")]
    private ?string $Username = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Le nom est obligatoire.")]
    private ?string $nom = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Le prénom est obligatoire.")]
    private ?string $prenom = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "L'email est obligatoire.")]
    #[Assert\Email(message: "L'email '{{ value }}' n'est pas une adresse email valide.")]
    private ?string $email = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $DateInscription = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $DateDerniereCOnnexion = null;

    #[ORM\Column(length: 255, nullable: true)]
private ?string $specialite = null;

#[ORM\Column(type: 'text', nullable: true)]
private ?string $experience = null;

// Add the getter and setter methods

public function getSpecialite(): ?string
{
    return $this->specialite;
}

public function setSpecialite(?string $specialite): static
{
    $this->specialite = $specialite;
    
    return $this;
}

public function getExperience(): ?string
{
    return $this->experience;
}

public function setExperience(?string $experience): static
{
    $this->experience = $experience;
    
    return $this;
}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->Username;
    }

    public function setUsername(string $Username): static
    {
        $this->Username = $Username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->Username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getDateInscription(): ?\DateTimeImmutable
    {
        return $this->DateInscription;
    }

    public function setDateInscription(?\DateTimeImmutable $DateInscription): static
    {
        $this->DateInscription = $DateInscription;

        return $this;
    }

    public function getDateDerniereCOnnexion(): ?\DateTimeImmutable
    {
        return $this->DateDerniereCOnnexion;
    }

    public function setDateDerniereCOnnexion(?\DateTimeImmutable $DateDerniereCOnnexion): static
    {
        $this->DateDerniereCOnnexion = $DateDerniereCOnnexion;

        return $this;
    }



    #[ORM\OneToMany(mappedBy: 'createur', targetEntity: Cours::class, orphanRemoval: true)]
    private Collection $coursCreated;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Certification::class, orphanRemoval: true)]
    private Collection $certifications;

    // Update the constructor (or add if it doesn't exist):
    public function __construct()
    {
        $this->coursCreated = new ArrayCollection();
        $this->certifications = new ArrayCollection();
    }

    // Add these methods at the end of the User class:

    /**
     * @return Collection<int, Cours>
     */
    public function getCoursCreated(): Collection
    {
        return $this->coursCreated;
    }

    public function addCoursCreated(Cours $cours): static
    {
        if (!$this->coursCreated->contains($cours)) {
            $this->coursCreated->add($cours);
            $cours->setCreateur($this);
        }
        return $this;
    }

    public function removeCoursCreated(Cours $cours): static
    {
        if ($this->coursCreated->removeElement($cours)) {
            if ($cours->getCreateur() === $this) {
                $cours->setCreateur(null);
            }
        }
        return $this;
    }

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
            $certification->setUser($this);
        }
        return $this;
    }

    public function removeCertification(Certification $certification): static
    {
        if ($this->certifications->removeElement($certification)) {
            if ($certification->getUser() === $this) {
                $certification->setUser(null);
            }
        }
        return $this;
    }

    #[ORM\OneToMany(mappedBy: 'etudiant', targetEntity: DemandeBourse::class, orphanRemoval: true)]
    private Collection $demandesBourse;

    /**
     * @return Collection<int, DemandeBourse>
     */
    public function getDemandesBourse(): Collection
    {
        return $this->demandesBourse;
    }

    public function addDemandeBourse(DemandeBourse $demandeBourse): static
    {
        if (!$this->demandesBourse->contains($demandeBourse)) {
            $this->demandesBourse->add($demandeBourse);
            $demandeBourse->setEtudiant($this);
        }

        return $this;
    }

    public function removeDemandeBourse(DemandeBourse $demandeBourse): static
    {
        if ($this->demandesBourse->removeElement($demandeBourse)) {
            // set the owning side to null (unless already changed)
            if ($demandeBourse->getEtudiant() === $this) {
                $demandeBourse->setEtudiant(null);
            }
        }

        return $this;
    }
}
