<?php
<<<<<<< HEAD
namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['Username'])]
=======

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
>>>>>>> ee09f695887cdbc96e92b8b02f40161029db34ed
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
<<<<<<< HEAD
    #[Assert\NotBlank(message: "Le numéro d'inscription (Username) est obligatoire.")]
    private ?string $Username = null;
=======
    private ?string $email = null;
>>>>>>> ee09f695887cdbc96e92b8b02f40161029db34ed

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

<<<<<<< HEAD
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $googleId = null;

=======
>>>>>>> ee09f695887cdbc96e92b8b02f40161029db34ed
    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

<<<<<<< HEAD
    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Le nom est obligatoire.")]
    private ?string $nom = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Le prénom est obligatoire.")]
    private ?string $prenom = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "L'email est obligatoire.")]
    #[Assert\Email(
        message: "L'email '{{ value }}' n'est pas valide."
    )]
    private ?string $email = null;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $isActive = true;

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

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
        return $this;
=======
    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $prenom = null;

    #[ORM\Column(length: 255)]
    private ?string $username = null;

    #[ORM\Column]
    private ?\DateTime $dateInscription = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $lastLogin = null;

    /**
     * @var Collection<int, Certification>
     */
    #[ORM\OneToMany(targetEntity: Certification::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $certifications;

    /**
     * @var Collection<int, DemandeBourse>
     */
    #[ORM\OneToMany(targetEntity: DemandeBourse::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $demandeBourses;

    public function __construct()
    {
        $this->certifications = new ArrayCollection();
        $this->demandeBourses = new ArrayCollection();
>>>>>>> ee09f695887cdbc96e92b8b02f40161029db34ed
    }

    public function getId(): ?int
    {
        return $this->id;
    }

<<<<<<< HEAD
    public function getUsername(): ?string
    {
        return $this->Username;
    }

    public function setUsername(string $Username): static
    {
        $this->Username = $Username;
=======
    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
>>>>>>> ee09f695887cdbc96e92b8b02f40161029db34ed

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
<<<<<<< HEAD
        return (string) $this->Username;
=======
        return (string) $this->email;
>>>>>>> ee09f695887cdbc96e92b8b02f40161029db34ed
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

<<<<<<< HEAD
    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
=======
    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;
>>>>>>> ee09f695887cdbc96e92b8b02f40161029db34ed

        return $this;
    }

<<<<<<< HEAD
    public function getDateInscription(): ?\DateTimeImmutable
    {
        return $this->DateInscription;
    }

    public function setDateInscription(?\DateTimeImmutable $DateInscription): static
    {
        $this->DateInscription = $DateInscription;
=======
    public function getDateInscription(): ?\DateTime
    {
        return $this->dateInscription;
    }

    public function setDateInscription(\DateTime $dateInscription): static
    {
        $this->dateInscription = $dateInscription;
>>>>>>> ee09f695887cdbc96e92b8b02f40161029db34ed

        return $this;
    }

<<<<<<< HEAD
    public function getDateDerniereCOnnexion(): ?\DateTimeImmutable
    {
        return $this->DateDerniereCOnnexion;
    }

    public function setDateDerniereCOnnexion(?\DateTimeImmutable $DateDerniereCOnnexion): static
    {
        $this->DateDerniereCOnnexion = $DateDerniereCOnnexion;
=======
    public function getLastLogin(): ?\DateTime
    {
        return $this->lastLogin;
    }

    public function setLastLogin(?\DateTime $lastLogin): static
    {
        $this->lastLogin = $lastLogin;
>>>>>>> ee09f695887cdbc96e92b8b02f40161029db34ed

        return $this;
    }

<<<<<<< HEAD


    #[ORM\OneToMany(mappedBy: 'createur', targetEntity: Cours::class, orphanRemoval: true)]
    private Collection $coursCreated;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Certification::class, orphanRemoval: true)]
    private Collection $certifications;

    // Update the constructor (or add if it doesn't exist):
    public function __construct()
    {
        $this->coursCreated = new ArrayCollection();
        $this->certifications = new ArrayCollection();
        $this->demandesBourse = new ArrayCollection();
        $this->sujets = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->notifications = new ArrayCollection();
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

=======
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
            $certification->setUser($this);
        }
<<<<<<< HEAD
=======

>>>>>>> ee09f695887cdbc96e92b8b02f40161029db34ed
        return $this;
    }

    public function removeCertification(Certification $certification): static
    {
        if ($this->certifications->removeElement($certification)) {
<<<<<<< HEAD
=======
            // set the owning side to null (unless already changed)
>>>>>>> ee09f695887cdbc96e92b8b02f40161029db34ed
            if ($certification->getUser() === $this) {
                $certification->setUser(null);
            }
        }
<<<<<<< HEAD
        return $this;
    }

    #[ORM\OneToMany(mappedBy: 'etudiant', targetEntity: DemandeBourse::class, orphanRemoval: true)]
    private Collection $demandesBourse;

    #[ORM\OneToMany(targetEntity: Sujet::class, mappedBy: 'auteur')]
    private Collection $sujets;

    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'auteur')]
    private Collection $messages;

    #[ORM\OneToMany(targetEntity: Notification::class, mappedBy: 'destinataire', cascade: ['remove'])]
    private Collection $notifications;

    /**
     * @return Collection<int, DemandeBourse>
     */
    public function getDemandesBourse(): Collection
    {
        return $this->demandesBourse;
=======

        return $this;
    }

    /**
     * @return Collection<int, DemandeBourse>
     */
    public function getDemandeBourses(): Collection
    {
        return $this->demandeBourses;
>>>>>>> ee09f695887cdbc96e92b8b02f40161029db34ed
    }

    public function addDemandeBourse(DemandeBourse $demandeBourse): static
    {
<<<<<<< HEAD
        if (!$this->demandesBourse->contains($demandeBourse)) {
            $this->demandesBourse->add($demandeBourse);
            $demandeBourse->setEtudiant($this);
=======
        if (!$this->demandeBourses->contains($demandeBourse)) {
            $this->demandeBourses->add($demandeBourse);
            $demandeBourse->setUser($this);
>>>>>>> ee09f695887cdbc96e92b8b02f40161029db34ed
        }

        return $this;
    }

    public function removeDemandeBourse(DemandeBourse $demandeBourse): static
    {
<<<<<<< HEAD
        if ($this->demandesBourse->removeElement($demandeBourse)) {
            // set the owning side to null (unless already changed)
            if ($demandeBourse->getEtudiant() === $this) {
                $demandeBourse->setEtudiant(null);
=======
        if ($this->demandeBourses->removeElement($demandeBourse)) {
            // set the owning side to null (unless already changed)
            if ($demandeBourse->getUser() === $this) {
                $demandeBourse->setUser(null);
>>>>>>> ee09f695887cdbc96e92b8b02f40161029db34ed
            }
        }

        return $this;
    }
<<<<<<< HEAD

    /**
     * @return Collection<int, Sujet>
     */
    public function getSujets(): Collection
    {
        return $this->sujets;
    }

    public function addSujet(Sujet $sujet): static
    {
        if (!$this->sujets->contains($sujet)) {
            $this->sujets->add($sujet);
            $sujet->setAuteur($this);
        }
        return $this;
    }

    public function removeSujet(Sujet $sujet): static
    {
        if ($this->sujets->removeElement($sujet)) {
            if ($sujet->getAuteur() === $this) {
                $sujet->setAuteur(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): static
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setAuteur($this);
        }
        return $this;
    }

    public function removeMessage(Message $message): static
    {
        if ($this->messages->removeElement($message)) {
            if ($message->getAuteur() === $this) {
                $message->setAuteur(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): static
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->setDestinataire($this);
        }
        return $this;
    }

    public function removeNotification(Notification $notification): static
    {
        if ($this->notifications->removeElement($notification)) {
            if ($notification->getDestinataire() === $this) {
                $notification->setDestinataire(null);
            }
        }
        return $this;
    }

    public function getGoogleId(): ?string
    {
        return $this->googleId;
    }

    public function setGoogleId(?string $googleId): static
    {
        $this->googleId = $googleId;

        return $this;
    }
=======
>>>>>>> ee09f695887cdbc96e92b8b02f40161029db34ed
}
