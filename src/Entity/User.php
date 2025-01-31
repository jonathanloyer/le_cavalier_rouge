<?php

namespace App\Entity;

use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['pseudo'], message: 'Ce pseudo est déjà utilisé.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: 'Votre email est obligatoire.')]
    #[Assert\Email(message: 'L\'email {{ value }} n\'est pas valide.')]
    private ?string $email = null;

    #[ORM\Column(type: 'json', nullable: false)]
    private array $roles = [];

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Votre nom est obligatoire.')]
    private ?string $lastName = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Votre prénom est obligatoire.')]
    private ?string $firstName = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(message: 'Votre pseudo est obligatoire.')]
    private ?string $pseudo = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $codeFFE = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ffeId = null;

    #[ORM\Column(type: 'boolean')]
    private ?bool $active = false;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $avatar = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\OneToMany(targetEntity: PlayerRole::class, mappedBy: 'user')]
    private Collection $playerRole;

    #[ORM\ManyToOne(targetEntity: Club::class, inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Club $club = null;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->playerRole = new ArrayCollection();
    }

    /**
     * Retourne l'identifiant unique de l'utilisateur.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Retourne l'email de l'utilisateur.
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Définit l'email de l'utilisateur.
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Retourne l'identifiant utilisateur visuel (email).
     */
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /**
     * Retourne le mot de passe haché de l'utilisateur.
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Définit le mot de passe haché de l'utilisateur.
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Retourne les rôles de l'utilisateur.
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    /**
     * Définit les rôles de l'utilisateur.
     */
    public function setRoles(array $roles): self
    {
        $this->roles = array_unique(array_merge($roles, ['ROLE_USER']));
        return $this;
    }

    /**
     * Efface les données sensibles (si nécessaire).
     */
    public function eraseCredentials(): void
    {
        // Exemple : $this->plainPassword = null;
    }

    /**
     * Retourne le nom de famille de l'utilisateur.
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * Définit le nom de famille de l'utilisateur.
     */
    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * Retourne le prénom de l'utilisateur.
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * Définit le prénom de l'utilisateur.
     */
    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * Retourne la date de création du compte.
     */
    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Retourne le club de l'utilisateur.
     */
    public function getClub(): ?Club
    {
        return $this->club;
    }

    /**
     * Définit le club de l'utilisateur.
     */
    public function setClub(?Club $club): self
    {
        $this->club = $club;
        return $this;
    }

    /**
     * Retourne si l'utilisateur est actif.
     */
    public function getActive(): ?bool
    {
        return $this->active;
    }

    /**
     * Définit si l'utilisateur est actif.
     */
    public function setActive(bool $active): self
    {
        $this->active = $active;
        return $this;
    }

    /**
     * Retourne le pseudo de l'utilisateur.
     */
    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    /**
     * Définit le pseudo de l'utilisateur.
     */
    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;
        return $this;
    }

    /**
     * Retourne le code FFE de l'utilisateur.
     */
    public function getCodeFFE(): ?string
    {
        return $this->codeFFE;
    }

    /**
     * Définit le code FFE de l'utilisateur.
     */
    public function setCodeFFE(?string $codeFFE): self
    {
        $this->codeFFE = $codeFFE;
        return $this;
    }

    /**
     * Retourne l'avatar de l'utilisateur.
     */
    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    /**
     * Définit l'avatar de l'utilisateur.
     */
    public function setAvatar(?string $avatar): self
    {
        $this->avatar = $avatar;
        return $this;
    }
    /**
 * Retourne les PlayerRoles associés à l'utilisateur.
 */
public function getPlayerRoles(): Collection
{
    return $this->playerRole;
}

/**
 * Ajoute un PlayerRole à l'utilisateur.
 */
public function addPlayerRole(PlayerRole $playerRole): self
{
    if (!$this->playerRole->contains($playerRole)) {
        $this->playerRole->add($playerRole);
        $playerRole->setUser($this); // Met à jour l'association dans PlayerRole
    }

    return $this;
}

/**
 * Supprime un PlayerRole de l'utilisateur.
 */
public function removePlayerRole(PlayerRole $playerRole): self
{
    if ($this->playerRole->removeElement($playerRole)) {
        // Si nécessaire, dissocier l'utilisateur dans PlayerRole
        if ($playerRole->getUser() === $this) {
            $playerRole->setUser(null);
        }
    }

    return $this;
}


    /**
     * Get the value of ffeId
     */ 
    public function getFfeId()
    {
        return $this->ffeId;
    }

    /**
     * Set the value of ffeId
     *
     * @return  self
     */ 
    public function setFfeId($ffeId)
    {
        $this->ffeId = $ffeId;

        return $this;
    }
}
