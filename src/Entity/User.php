<?php

namespace App\Entity;

use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\ManyToOne(targetEntity: Role::class)]
    #[ORM\JoinColumn(nullable: false)]
    private array $roles = [];

    #[ORM\Column(length: 100)]
    private ?string $lastName = null;

    #[ORM\Column(length: 100)]
     private ?string $firstName = null;
     #[ORM\Column]
     private ?string $pseudo;
 

    #[ORM\Column(length: 255)]
    private ?string $ffeId = null;

    #[ORM\Column(type:'boolean')]
    private ?bool $active = null;

    #[ORM\ManyToOne(targetEntity: Club::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?string $club = null;

    #[ORM\Column(type: 'datetime')]
private ?DateTimeImmutable $createdAt = null;





    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

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

    /**
     * @see UserInterface
     * @return list<string>
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
public function getCreatedAt()
{
return $this->createdAt;
}

/**
 * Set the value of createdAt
 *
 * @return  self
 */ 
public function setCreatedAt($createdAt)
{
$this->createdAt = $createdAt;

return $this;
}

    /**
     * Get the value of club
     */ 
    public function getClub()
    {
        return $this->club;
    }

    /**
     * Set the value of club
     *
     * @return  self
     */ 
    public function setClub($club)
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
}
