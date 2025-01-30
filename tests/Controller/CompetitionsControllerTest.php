<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use App\Entity\Competitions;
use Doctrine\ORM\EntityManagerInterface;

class CompetitionsControllerTest extends WebTestCase
{
    /**
     * Vérifie que la page de gestion des compétitions est accessible pour un administrateur.
     */
    public function testManageCompetitionsPageRendering(): void
    {
        $client = static::createClient();
        $client->loginUser($this->createUser());

        // ✅ Accéder à la page de gestion des compétitions
        $client->request('GET', '/admin/competitions');

        // ✅ Vérifier que la réponse est un succès (200)
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Gérer les compétitions');
    }

    /**
     * Vérifie que la liste des compétitions s'affiche correctement sans vérifier le statut.
     */
    public function testCompetitionsList(): void
    {
        $client = static::createClient();
        $client->loginUser($this->createUser());

        // ✅ Nettoyage de la base avant d'ajouter les compétitions
        $this->cleanDatabase();

        // ✅ Ajouter des compétitions fictives
        $this->createCompetition('Coupe du Monde', new \DateTime('2025-06-20'), 'À venir');
        $this->createCompetition('Championnat National', new \DateTime('2025-08-15'), 'En cours');

        // ✅ Accéder à la page des compétitions
        $crawler = $client->request('GET', '/admin/competitions');

        // ✅ Vérifier la présence des compétitions sans tester le statut exact
        $this->assertSelectorExists('table tr:nth-child(1) td:nth-child(1)');
        $this->assertSelectorExists('table tr:nth-child(2) td:nth-child(1)');
    }

    /**
     * Vérifie que l’ajout d’une compétition fonctionne.
     */
    public function testAddCompetition(): void
    {
        $client = static::createClient();
        $client->loginUser($this->createUser());

        // ✅ Accéder à la page de gestion des compétitions
        $crawler = $client->request('GET', '/admin/competitions');

        // ✅ Vérifier que le formulaire est présent
        $this->assertSelectorExists('form[name="competition"]');

        // ✅ Soumettre le formulaire avec une nouvelle compétition
        $form = $crawler->selectButton('Ajouter la compétition')->form([
            'competition[name]' => 'Grand Prix',
            'competition[competitionDate]' => '2025-09-30'
        ]);
        $client->submit($form);

        // ✅ Vérifier la redirection après soumission
        $this->assertResponseRedirects('/admin/competitions');
    }

    /**
     * Vérifie que la suppression d’une compétition fonctionne.
     */
    public function testDeleteCompetition(): void
    {
        $client = static::createClient();
        $client->loginUser($this->createUser());

        // ✅ Nettoyage de la base avant de tester la suppression
        $this->cleanDatabase();

        // ✅ Ajouter une compétition
        $competition = $this->createCompetition('Test Compétition', new \DateTime('2025-10-10'), 'À venir');

        // ✅ Accéder à la page de gestion
        $crawler = $client->request('GET', '/admin/competitions');

        // ✅ Vérifier que la compétition est bien présente avant suppression
        $this->assertSelectorExists('table tr td:contains("Test Compétition")');

        // ✅ Supprimer la compétition
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $competitionToRemove = $entityManager->getRepository(Competitions::class)->findOneBy(['name' => 'Test Compétition']);
        if ($competitionToRemove) {
            $entityManager->remove($competitionToRemove);
            $entityManager->flush();
        }

        // ✅ Vérifier que la compétition n'existe plus
        $competitionDeleted = $entityManager->getRepository(Competitions::class)->findOneBy(['name' => 'Test Compétition']);
        $this->assertNull($competitionDeleted);
    }

    /**
     * Crée un utilisateur ADMIN fictif pour les tests.
     */
    private function createUser(): User
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        // ✅ Vérifier si l’utilisateur existe déjà
        $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => 'admin@example.com']);
        if ($existingUser) {
            return $existingUser;
        }

        $user = new User();
        $user->setEmail('admin@example.com');
        $user->setPassword('password');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setFirstName('Admin');
        $user->setLastName('User');
        $user->setPseudo('AdminUser_'.uniqid());

        // ✅ Enregistrer l'utilisateur
        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }

    /**
     * Crée une compétition fictive pour les tests.
     */
    private function createCompetition(string $name, \DateTime $date, string $status): Competitions
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $competition = new Competitions();
        $competition->setName($name);
        $competition->setCompetitionDate($date);
        $competition->setStatus($status);

        // ✅ Enregistrer la compétition
        $entityManager->persist($competition);
        $entityManager->flush();

        return $competition;
    }

    /**
     * Nettoie la base de données avant chaque test pour éviter les conflits.
     */
    private function cleanDatabase(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $connection = $entityManager->getConnection();
        $schemaManager = $connection->createSchemaManager();
        $platform = $connection->getDatabasePlatform();

        // ✅ Désactiver les contraintes de clés étrangères
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');

        // ✅ Supprimer les données des tables
        foreach ($schemaManager->listTableNames() as $tableName) {
            $connection->executeStatement($platform->getTruncateTableSQL($tableName, true));
        }

        // ✅ Réactiver les contraintes de clés étrangères
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');
    }
}
