<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;

#[ODM\Document(collection: "contact")]
class Contact
{
    #[ODM\Id]
    private $id;

    #[ODM\Field(type: "string")]
    #[Assert\NotBlank(message: "Le nom ne peut pas être vide.")]
    #[Assert\Length(min: 3, max: 255, minMessage: "Le nom doit contenir au moins {{ limit }} caractères.", maxMessage: "Le nom ne peut pas contenir plus de {{ limit }} caractères.")]
    private $name;

    #[ODM\Field(type: "string")]
    #[Assert\NotBlank(message: "Le prénom ne peut pas être vide.")]
    #[Assert\Length(min: 3, max: 255, minMessage: "Le prénom doit contenir au moins {{ limit }} caractères.", maxMessage: "Le prénom ne peut pas contenir plus de {{ limit }} caractères.")]
    private $firstname;

    #[ODM\Field(type: "string")]
    #[Assert\NotBlank(message: "L'email ne peut pas être vide.")]
    #[Assert\Email(message: "Veuillez entrer une adresse email valide.")]
    private $email;

    #[ODM\Field(type: "string")]
    #[Assert\NotBlank(message: "Le message ne peut pas être vide.")]
    #[Assert\Length(min: 10, max: 1000, minMessage: "Le message doit contenir au moins {{ limit }} caractères.", maxMessage: "Le message ne peut pas contenir plus de {{ limit }} caractères.")]
    private $message;

    #[ODM\Field(type: "date")]
    private $createdAt;

    // Getters et Setters

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
        $this->name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');  // Assainissement du nom
        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = htmlspecialchars($firstname, ENT_QUOTES, 'UTF-8');  // Assainissement du prénom
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        // Assainissement du message pour éviter toute injection HTML
        $this->message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
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
