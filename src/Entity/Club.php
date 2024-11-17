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
     * @var Collection<int, FeuilleMatch>
     */
    #[ORM\OneToMany(targetEntity: FeuilleMatch::class, mappedBy: 'clubA')]
    private Collection $clubA;

    /**
     * @var Collection<int, FeuilleMatch>
     */
    #[ORM\OneToMany(targetEntity: FeuilleMatch::class, mappedBy: 'clubB')]
    private Collection $clubB;

    /**
     * @var Collection<int, Joueurs>
     */
    #[ORM\OneToMany(targetEntity: Joueurs::class, mappedBy: 'club')]
    private Collection $club;

    public function __construct()
    {
        $this->clubA = new ArrayCollection();
        $this->clubB = new ArrayCollection();
        $this->club = new ArrayCollection();
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
     * @return Collection<int, FeuilleMatch>
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
            // set the owning side to null (unless already changed)
            if ($clubA->getClubA() === $this) {
                $clubA->setClubA(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FeuilleMatch>
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
            // set the owning side to null (unless already changed)
            if ($clubB->getClubB() === $this) {
                $clubB->setClubB(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Joueurs>
     */
    public function getClub(): Collection
    {
        return $this->club;
    }

    public function addClub(Joueurs $club): static
    {
        if (!$this->club->contains($club)) {
            $this->club->add($club);
            $club->setClub($this);
        }

        return $this;
    }

    public function removeClub(Joueurs $club): static
    {
        if ($this->club->removeElement($club)) {
            // set the owning side to null (unless already changed)
            if ($club->getClub() === $this) {
                $club->setClub(null);
            }
        }

        return $this;
    }
}
