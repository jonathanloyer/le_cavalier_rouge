<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher, private EntityManagerInterface $entityManager) {}

    public function load(ObjectManager $manager): void
    {
        // Supprimer les utilisateurs fictifs existants avec une requête SQL brute
        $conn = $this->entityManager->getConnection();
        $conn->executeStatement('DELETE FROM user WHERE pseudo LIKE :prefix', ['prefix' => 'user%']);
        echo "Existing fictitious users removed.\n";

        // Vérifier et insérer un utilisateur de test
        $existingTestUser = $manager->getRepository(User::class)->findOneBy(['pseudo' => 'testuser']);
        if (!$existingTestUser) {
            $user = new User();
            $user->setEmail('testuser@example.com')
                ->setPseudo('testuser')
                ->setFirstName('Test')
                ->setLastName('User')
                ->setPassword($this->passwordHasher->hashPassword($user, 'password123'))
                ->setRoles(['ROLE_USER'])
                ->setActive(true);

            $manager->persist($user);
            echo "User 'testuser' created.\n";
        }

        // Vérifier et insérer un administrateur
        $existingAdmin = $manager->getRepository(User::class)->findOneBy(['pseudo' => 'admin']);
        if (!$existingAdmin) {
            $admin = new User();
            $admin->setEmail('admin@example.com')
                ->setPseudo('admin')
                ->setFirstName('Admin')
                ->setLastName('User')
                ->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'))
                ->setRoles(['ROLE_ADMIN'])
                ->setActive(true);

            $manager->persist($admin);
            echo "Admin 'admin' created.\n";
        }

        // Générer des utilisateurs fictifs
        for ($i = 1; $i <= 5; $i++) {
            $user = new User();
            $user->setEmail("user{$i}@example.com")
                ->setPseudo("user{$i}")
                ->setFirstName("User{$i}")
                ->setLastName("Test{$i}")
                ->setPassword($this->passwordHasher->hashPassword($user, 'password123'))
                ->setRoles(['ROLE_USER'])
                ->setActive(true);

            $manager->persist($user);
            echo "Fictitious user 'user{$i}' created.\n";
        }

        // Sauvegarder les modifications
        $manager->flush();
    }
}
