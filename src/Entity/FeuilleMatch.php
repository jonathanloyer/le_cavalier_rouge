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

    #[ORM\Column(length: 50)]
    private ?string $type = null; // "criterium" ou "national"

    #[ORM\Column(length: 50)]
    private ?string $groupe = null; // Groupe 1 ou Groupe 2

    #[ORM\Column(length: 50)]
    private ?string $interclub = null; // "Interclub Jeune", etc.

    #[ORM\Column(type: 'json')] // Stocker un tableau JSON pour les joueurs, json est un format de données léger afin de stocker des données structurées 
    private array $joueurs = []; // Liste des joueurs et résultats

    // Constructeur pour initialiser les propriétés par défaut
    public function __construct()
    {
        $this->joueurs = [];
    }

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getGroupe(): ?string
    {
        return $this->groupe;
    }

    public function setGroupe(string $groupe): static
    {
        $this->groupe = $groupe;

        return $this;
    }

    public function getInterclub(): ?string
    {
        return $this->interclub;
    }

    public function setInterclub(string $interclub): static
    {
        $this->interclub = $interclub;

        return $this;
    }

    public function getJoueurs(): array
    {
        return $this->joueurs;
    }

    public function setJoueurs(array $joueurs): static
    {
        // Vérifiez que chaque élément du tableau contient les clés nécessaires
        foreach ($joueurs as &$joueur) {
            if (!isset($joueur['id'])) {
                $joueur['id'] = null; // Ajoutez une valeur par défaut
            }
            if (!isset($joueur['role'])) {
                $joueur['role'] = null; // Ajoutez une valeur par défaut
            }
            if (!isset($joueur['resultat'])) {
                $joueur['resultat'] = null; // Ajoutez une valeur par défaut
            }
        }
    
        $this->joueurs = $joueurs;
    
        return $this;
    }
    
}
