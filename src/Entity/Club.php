<?php

namespace App\Entity;

use App\Repository\ClubRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClubRepository::class)]
class Club
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, FeuilleMatch> Liste des matchs où ce club est ClubA
     */
    #[ORM\OneToMany(targetEntity: FeuilleMatch::class, mappedBy: 'clubA')]
    private Collection $clubA;

    /**
     * @var Collection<int, FeuilleMatch> Liste des matchs où ce club est ClubB
     */
    #[ORM\OneToMany(targetEntity: FeuilleMatch::class, mappedBy: 'clubB')]
    private Collection $clubB;

    /**
     * @var Collection<int, Joueurs> Liste des joueurs appartenant au club
     */
    #[ORM\OneToMany(targetEntity: Joueurs::class, mappedBy: 'club')]
    private Collection $joueurs;

    /**
     * @var Collection<int, User> Liste des utilisateurs associés au club
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'club')]
    private Collection $users;

    public function __construct()
    {
        $this->clubA = new ArrayCollection();
        $this->clubB = new ArrayCollection();
        $this->joueurs = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, FeuilleMatch> Matchs où ce club est ClubA
     */
    public function getClubA(): Collection
    {
        return $this->clubA;
    }

    public function addClubA(FeuilleMatch $clubA): static
    {
        if (!$this->clubA->contains($clubA)) {
            $this->clubA->add($clubA);
            $clubA->setClubA($this);
        }

        return $this;
    }

    public function removeClubA(FeuilleMatch $clubA): static
    {
        if ($this->clubA->removeElement($clubA)) {
            if ($clubA->getClubA() === $this) {
                $clubA->setClubA(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FeuilleMatch> Matchs où ce club est ClubB
     */
    public function getClubB(): Collection
    {
        return $this->clubB;
    }

    public function addClubB(FeuilleMatch $clubB): static
    {
        if (!$this->clubB->contains($clubB)) {
            $this->clubB->add($clubB);
            $clubB->setClubB($this);
        }

        return $this;
    }

    public function removeClubB(FeuilleMatch $clubB): static
    {
        if ($this->clubB->removeElement($clubB)) {
            if ($clubB->getClubB() === $this) {
                $clubB->setClubB(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Joueurs> Liste des joueurs du club
     */
    public function getJoueurs(): Collection
    {
        return $this->joueurs;
    }

    public function addJoueur(Joueurs $joueur): static
    {
        if (!$this->joueurs->contains($joueur)) {
            $this->joueurs->add($joueur);
            $joueur->setClub($this);
        }

        return $this;
    }

    public function removeJoueur(Joueurs $joueur): static
    {
        if ($this->joueurs->removeElement($joueur)) {
            if ($joueur->getClub() === $this) {
                $joueur->setClub(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, User> Liste des utilisateurs associés au club
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setClub($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            if ($user->getClub() === $this) {
                $user->setClub(null);
            }
        }

        return $this;
    }
}
