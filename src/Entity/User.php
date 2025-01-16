<?php

namespace App\Entity;

use App\Repository\UserRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface,
PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;
    
    #[ORM\Column(type: 'json', nullable:false)]
    private array $roles = [];

    #[ORM\OneToMany(targetEntity: PlayerRole::class, mappedBy: 'user')]
    private Collection $playerRole;

    #[ORM\Column(length: 100)]
    private ?string $lastName = null;

    #[ORM\Column(length: 100)]
    private ?string $firstName = null;
    #[ORM\Column]
    private ?string $pseudo;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $codeFFE = null;

   

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ffeId = null;

    #[ORM\Column(type: 'boolean')]
    private ?bool $active = false;
    
    #[ORM\Column(length: 180, nullable: true)]
    private ?string $avatar = null;


    #[ORM\ManyToOne(targetEntity: Club::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Club $club = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: false)]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?string $password;

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }
    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable(); // Utilisation de DateTimeImmutable
        $this->playerRole = new ArrayCollection();
    }

    public function getPlayerRoles()
    {
        // $roles = $this->roles;
        // garantir que chaque utilisateur a au moins ROLE_USER
        // $roles[] = 'ROLE_USER';
        return $this->playerRole;
        // return array_unique($roles);
    }


    public function addPlayerRoles(PlayerRole $playerRole)
    {
        $playerRole->setUser($this);
        $this->playerRole->add($playerRole);

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

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * Get the value of lastName
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set the value of lastName
     *
     * @return  self
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get the value of firstName
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set the value of firstName
     *
     * @return  self
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }


    /**
     * Get the value of createdAt
     */
    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Set the value of createdAt
     *
     * @return  self
     */
    public function setCreatedAt(DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }



    /**
     * Get the value of club
     */
    public function getClub(): ?Club
    {
        return $this->club;
    }

    /**
     * Set the value of club
     *
     * @return  self
     */
    public function setClub(?Club $club): self
    {
        $this->club = $club;

        return $this;
    }

    /**
     * Get the value of active
     */
    public function getActive()
    {
        return $this->active;
    }
    /**
     * Set the value of active
     *
     * @return  self
     */
    public function setActive($active)
    {
        $this->active = $active;

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

    /**
     * Get the value of pseudo
     */
    public function getPseudo()
    {
        return $this->pseudo;
    }

    /**
     * Set the value of pseudo
     *
     * @return  self
     */
    public function setPseudo($pseudo)
    {
        $this->pseudo = $pseudo;

        return $this;
    }

//     /**
//      * Get the value of roles
//      */ 
//     *  @see UserInterface
//    */
  public function getRoles(): array {
    $roles = $this->roles;
    // Yous les utilisateur on au moin le ROLE_USER
    $roles[] = 'ROLE_USER';

    return array_unique($roles);
  }
    /**
     * Set the value of roles
     *
     * @return  self
     */ 
    public function setRoles($roles):self
    {
        // Ajout automatique du rôle "ROLE_USER" si absent
    $this->roles = array_unique(array_merge($roles, ['ROLE_USER']));

        return $this;
    }

    /**
     * Get the value of avatar
     */ 
    public function getAvatar():?string
    {
        return $this->avatar;
    }

    /**
     * Set the value of avatar
     *
     * @return  self
     */ 
    public function setAvatar($avatar):static
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * Get the value of codeFFE
     */ 
    public function getCodeFFE(): ?string    
    {
        return $this->codeFFE;
    }

    /**
     * Set the value of codeFFE
     *
     * @return  self
     */ 
    public function setCodeFFE(?string $codeFFE): self
    {
        $this->codeFFE = $codeFFE;

        return $this;
    }
}
