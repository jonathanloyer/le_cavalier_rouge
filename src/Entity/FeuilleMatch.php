<?php

namespace App\Entity;

use App\Repository\FeuilleMatchRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FeuilleMatchRepository::class)]
class FeuilleMatch
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'clubA')]
    private ?Club $clubA = null;

    #[ORM\ManyToOne(inversedBy: 'clubB')]
    private ?Club $clubB = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $creation = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateMatch = null;

    #[ORM\Column]
    private ?int $ronde = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClubA(): ?Club
    {
        return $this->clubA;
    }

    public function setClubA(?Club $clubA): static
    {
        $this->clubA = $clubA;

        return $this;
    }

    public function getClubB(): ?Club
    {
        return $this->clubB;
    }

    public function setClubB(?Club $clubB): static
    {
        $this->clubB = $clubB;

        return $this;
    }

    public function getCreation(): ?\DateTimeImmutable
    {
        return $this->creation;
    }

    public function setCreation(\DateTimeImmutable $creation): static
    {
        $this->creation = $creation;

        return $this;
    }

    public function getDateMatch(): ?\DateTimeImmutable
    {
        return $this->dateMatch;
    }

    public function setDateMatch(\DateTimeImmutable $dateMatch): static
    {
        $this->dateMatch = $dateMatch;

        return $this;
    }

    public function getRonde(): ?int
    {
        return $this->ronde;
    }

    public function setRonde(int $ronde): static
    {
        $this->ronde = $ronde;

        return $this;
    }
}
