<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert; // 🚀 Ajout de Symfony Validator

#[ODM\Document(collection: "contact")] // 🔥 On force une seule collection
class Contact
{
    #[ODM\Id]
    private $id;

    #[ODM\Field(type: "string")]
    #[Assert\NotBlank(message: "Le nom est obligatoire.")]
    #[Assert\Length(max: 100, maxMessage: "Le nom ne peut pas dépasser 100 caractères.")]
    private $name;

    #[ODM\Field(type: "string")]
    #[Assert\NotBlank(message: "Le prénom est obligatoire.")]
    #[Assert\Length(max: 100, maxMessage: "Le prénom ne peut pas dépasser 100 caractères.")]
    private $firstname; // 🔥 Nouveau champ pour le prénom

    #[ODM\Field(type: "string")]
    #[Assert\NotBlank(message: "L'email est obligatoire.")]
    #[Assert\Email(message: "L'email n'est pas valide.")]
    private $email;

    #[ODM\Field(type: "string")]
    #[Assert\NotBlank(message: "Le message est obligatoire.")]
    #[Assert\Length(max: 1000, maxMessage: "Le message ne peut pas dépasser 1000 caractères.")]
    private $message;

    #[ODM\Field(type: "date")]
    private $createdAt;

    // 🔥 Getters et Setters avec validation XSS et protection

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = strip_tags($name); // 🔥 Supprime les balises HTML pour éviter le XSS
        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = strip_tags($firstname); // 🔥 Supprime les balises HTML pour éviter le XSS
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = filter_var($email, FILTER_SANITIZE_EMAIL); // 🔥 Nettoie l'email
        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); // 🔥 Encode les caractères dangereux
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}
