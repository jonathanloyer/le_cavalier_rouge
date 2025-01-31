<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\DBAL\Connection;

class LoginUserFixture extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;
    private Connection $connection;

    // Injecter le service UserPasswordHasherInterface pour encoder les mots de passe et la connexion DBAL
    public function __construct(UserPasswordHasherInterface $passwordHasher, Connection $connection)
    {
        $this->passwordHasher = $passwordHasher;
        $this->connection = $connection;
    }

    public function load(ObjectManager $manager): void
    {
        // Supprimer les utilisateurs fictifs existants avec une requête SQL brute
        $this->connection->executeStatement('DELETE FROM user WHERE pseudo LIKE :prefix', ['prefix' => 'user%']);
        echo "Existing fictitious users removed.\n";

        // Vérifier et insérer un utilisateur de test (utilisé pour testSuccessfulLogin)
        $existingTestUser = $manager->getRepository(User::class)->findOneBy(['pseudo' => 'testuser']);
        if (!$existingTestUser) {
            $user = new User();
            $user->setEmail('testuser@example.com')
                ->setPseudo('testuser')
                ->setPassword($this->passwordHasher->hashPassword($user, 'password123')) // Mot de passe haché
                ->setRoles(['ROLE_USER'])
                ->setActive(true);

            $manager->persist($user);
            echo "User 'testuser' created.\n";
        }

        // Vérifier et insérer un utilisateur invalide (utilisé pour testLoginWithInvalidCredentials)
        $existingInvalidUser = $manager->getRepository(User::class)->findOneBy(['pseudo' => 'wrongpseudo']);
        if (!$existingInvalidUser) {
            $invalidUser = new User();
            $invalidUser->setEmail('wrongemail@example.com')
                ->setPseudo('wrongpseudo')
                ->setPassword($this->passwordHasher->hashPassword($invalidUser, 'wrongpassword')) // Mot de passe incorrect
                ->setRoles(['ROLE_USER'])
                ->setActive(true);

            $manager->persist($invalidUser);
            echo "Invalid user 'wrongpseudo' created.\n";
        }

        // Sauvegarder les utilisateurs dans la base de données
        $manager->flush();
    }
}
