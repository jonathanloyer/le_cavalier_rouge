<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour l'entité User.
 *
 * Ces tests couvrent les méthodes principales de l'entité User, 
 * notamment les getters, setters et certaines règles spécifiques.
 */
class UserTest extends TestCase
{
    /**
     * Teste les méthodes getPseudo et setPseudo.
     */
    public function testPseudoGetterAndSetter(): void
    {
        $user = new User();
        $user->setPseudo('Jonathan');
        $this->assertEquals('Jonathan', $user->getPseudo());
    }

    /**
     * Teste que le rôle ROLE_USER est automatiquement ajouté.
     */
    public function testSetRolesAutomaticallyAddsRoleUser(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_ADMIN']);
        $this->assertContains('ROLE_USER', $user->getRoles());
        $this->assertContains('ROLE_ADMIN', $user->getRoles());
    }

    /**
     * Vérifie que la date de création est correctement initialisée dans le constructeur.
     */
    public function testCreatedAtIsSetInConstructor(): void
    {
        $user = new User();
        $this->assertInstanceOf(\DateTimeImmutable::class, $user->getCreatedAt());
    }

    /**
     * Teste les méthodes getActive et setActive.
     */
    public function testSetAndGetActive(): void
    {
        $user = new User();
        $user->setActive(true);
        $this->assertTrue($user->getActive());
    }

    /**
     * Teste les méthodes getEmail et setEmail.
     */
    public function testEmailGetterAndSetter(): void
    {
        $user = new User();
        $user->setEmail('jonathan@example.com');
        $this->assertEquals('jonathan@example.com', $user->getEmail());
    }

    /**
     * Teste les méthodes getLastName et setLastName.
     */
    public function testLastNameGetterAndSetter(): void
    {
        $user = new User();
        $user->setLastName('Loyer');
        $this->assertEquals('Loyer', $user->getLastName());
    }

    /**
     * Teste les méthodes getFirstName et setFirstName.
     */
    public function testFirstNameGetterAndSetter(): void
    {
        $user = new User();
        $user->setFirstName('Jonathan');
        $this->assertEquals('Jonathan', $user->getFirstName());
    }

    /**
     * Teste les méthodes getAvatar et setAvatar.
     */
    public function testAvatarGetterAndSetter(): void
    {
        $user = new User();
        $user->setAvatar('avatar.png');
        $this->assertEquals('avatar.png', $user->getAvatar());
    }

    /**
     * Teste les méthodes getCodeFFE et setCodeFFE.
     */
    public function testCodeFFEGetterAndSetter(): void
    {
        $user = new User();
        $user->setCodeFFE('12345');
        $this->assertEquals('12345', $user->getCodeFFE());
    }

    /**
     * Teste les relations entre User et Club.
     */
    public function testClubGetterAndSetter(): void
    {
        $club = new \App\Entity\Club();
        $user = new User();
        $user->setClub($club);
        $this->assertSame($club, $user->getClub());
    }

    /**
     * Teste l'ajout et la récupération des PlayerRoles.
     */
    public function testAddAndGetPlayerRoles(): void
{
    $playerRole = new \App\Entity\PlayerRole();
    $user = new User();

    // Utilisez la méthode correcte `addPlayerRole`
    $user->addPlayerRole($playerRole);

    $this->assertCount(1, $user->getPlayerRoles());
    $this->assertSame($playerRole, $user->getPlayerRoles()[0]);
}

    /**
     * Teste la méthode getUserIdentifier.
     */
    public function testGetUserIdentifier(): void
    {
        $user = new User();
        $user->setEmail('identifier@example.com');
        $this->assertEquals('identifier@example.com', $user->getUserIdentifier());
    }
}
