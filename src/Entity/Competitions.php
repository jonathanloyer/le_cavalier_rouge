<?php

namespace App\Entity;

use App\Repository\CompetitionsRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: CompetitionsRepository::class)]
class Competitions
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $status = 'En attente';

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotNull]
    private ?\DateTimeInterface $competitionDate = null;
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

    // Getter et setter pour "status"
    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the value of competitionDate
     */
    public function getCompetitionDate(): ?\DateTimeInterface
    {
        return $this->competitionDate;
    }


    /**
     * Set the value of competitionDate
     *
     * @return  self
     */
    public function setCompetitionDate(\DateTimeInterface $competitionDate): static
    {
        $this->competitionDate = $competitionDate;

        return $this;
    }
}
