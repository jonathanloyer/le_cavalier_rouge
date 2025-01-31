<?php

namespace App\Tests\Controller;

use App\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Tests unitaires pour l'AuthController.
 * Ce fichier contient des exemples de tests unitaires utilisant PHPUnit.
 * 
 * Documentation de PHPUnit : https://phpunit.de/manual/current/en/index.html
 */
class AuthControllerTest extends TestCase
{
    private $passwordHasher;

    /**
     * Configuration préalable aux tests.
     * Utilisation d'un mock pour simuler le service UserPasswordHasherInterface.
     * 
     * @see https://phpunit.readthedocs.io/en/9.6/test-doubles.html
     */
    protected function setUp(): void
    {
        // Mock du service de hachage des mots de passe
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);

        // Configuration du mock pour la méthode hashPassword
        $this->passwordHasher
            ->expects($this->any()) // La méthode peut être appelée plusieurs fois
            ->method('hashPassword')
            ->willReturn('hashed_password');
    }

    /**
     * Test unitaire pour la création d'un utilisateur.
     * Vérifie que toutes les propriétés de l'utilisateur sont correctement définies.
     */
    public function testUserCreation(): void
    {
        // Mock d'un utilisateur
        $user = new User();
        $user->setEmail('testuser@example.com');
        $user->setPseudo('testuser');
        $user->setFirstName('John');
        $user->setLastName('Doe');
        $user->setActive(true);

        // Appel de la méthode hashPassword simulée
        $hashedPassword = $this->passwordHasher->hashPassword($user, 'password123');
        $user->setPassword($hashedPassword);

        // Assertions sur les propriétés de l'utilisateur
        $this->assertEquals('testuser@example.com', $user->getEmail()); // Vérifie l'email
        $this->assertEquals('testuser', $user->getPseudo());            // Vérifie le pseudo
        $this->assertEquals('John', $user->getFirstName());             // Vérifie le prénom
        $this->assertEquals('Doe', $user->getLastName());               // Vérifie le nom
        $this->assertTrue($user->getActive());                          // Vérifie que le compte est actif
        $this->assertEquals('hashed_password', $user->getPassword());   // Vérifie le mot de passe haché
    }

    /**
     * Test unitaire pour l'attribution des rôles d'un utilisateur.
     * Vérifie que les rôles sont correctement définis et uniques.
     */
    public function testSetRoles(): void
    {
        // Mock d'un utilisateur
        $user = new User();

        // Définir des rôles
        $user->setRoles(['ROLE_USER', 'ROLE_ADMIN']);

        // Vérifier les rôles (ROLE_USER doit toujours être inclus)
        $this->assertContains('ROLE_USER', $user->getRoles()); // Vérifie le rôle USER
        $this->assertContains('ROLE_ADMIN', $user->getRoles()); // Vérifie le rôle ADMIN
        $this->assertCount(2, $user->getRoles());               // Vérifie qu'il y a 2 rôles uniques
    }

    /**
     * Test unitaire pour le hachage de mot de passe.
     * Vérifie que le mot de passe haché est bien attribué à l'utilisateur.
     */
    public function testPasswordHashing(): void
    {
        // Mock d'un utilisateur
        $user = new User();
        $user->setEmail('testuser@example.com');

        // Appel de la méthode hashPassword simulée
        $hashedPassword = $this->passwordHasher->hashPassword($user, 'password123');
        $user->setPassword($hashedPassword);

        // Vérification du mot de passe
        $this->assertEquals('hashed_password', $user->getPassword()); // Vérifie le mot de passe haché
    }
}
