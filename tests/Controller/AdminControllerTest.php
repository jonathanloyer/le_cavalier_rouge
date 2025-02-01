<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class AdminControllerTest extends WebTestCase
{
    private $client; // Stocke le client de test Symfony
    private $entityManager; // Stocke l'EntityManager pour interagir avec la base de données

    protected function setUp(): void
    {
        parent::setUp();

        // Initialise le client pour les requêtes HTTP
        $this->client = static::createClient();

        // Récupère l'EntityManager à partir du conteneur de services
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();
    }

    public function testAdminIndexPage(): void
    {
        // Crée un utilisateur avec le rôle ADMIN
        $user = $this->createUser(['ROLE_ADMIN']);

        // Connecte l'utilisateur au client pour simuler une session
        $this->client->loginUser($user);

        // Effectue une requête GET sur la page admin
        $crawler = $this->client->request('GET', '/admin');

        // Vérifie que la réponse HTTP est réussie (code 200)
        $this->assertResponseIsSuccessful();

        // Vérifie que la balise <h1> contient le texte attendu
        $this->assertSelectorTextContains('h1', 'Tableau de bord - Administration');

        // Vérifie qu'un élément avec la classe CSS `.grid` est présent sur la page
        $this->assertSelectorExists('.grid', 'Le tableau de bord doit contenir un résumé.');
    }

    public function testAccessDeniedForNonAdmin(): void
    {
        // Crée un utilisateur sans le rôle ADMIN
        $user = $this->createUser(['ROLE_USER']);

        // Connecte l'utilisateur au client pour simuler une session
        $this->client->loginUser($user);

        // Effectue une requête GET sur la page admin
        $this->client->request('GET', '/admin');

        // Vérifie que la réponse HTTP retourne le code 403 (accès refusé)
        $this->assertResponseStatusCodeSame(403, 'Un utilisateur sans rôle ADMIN ne peut pas accéder à la page admin.');
    }

    public function testRedirectionForUnauthenticatedUsers(): void
    {
        // Effectue une requête GET sur la page admin sans utilisateur connecté
        $this->client->request('GET', '/admin');

        // Vérifie que l'utilisateur est redirigé vers la page de connexion (code 302)
        $this->assertResponseRedirects('/login', 302, 'Les utilisateurs non authentifiés doivent être redirigés vers la page de connexion.');
    }

    private function createUser(array $roles): User
    {
        // Crée une nouvelle instance de l'entité User
        $user = new User();

        // Définit un email unique pour l'utilisateur
        $user->setEmail('test' . uniqid() . '@example.com');

        // Associe les rôles spécifiés à l'utilisateur
        $user->setRoles($roles);

        // Définit le prénom et le nom de l'utilisateur
        $user->setFirstName('Test');
        $user->setLastName('User');

        // Définit un pseudo unique pour l'utilisateur
        $user->setPseudo('TestUser_' . uniqid());

        // Récupère le service de hachage de mot de passe et encode un mot de passe
        $encoder = static::getContainer()->get('security.user_password_hasher');
        $user->setPassword($encoder->hashPassword($user, 'password'));

        // Persiste l'utilisateur dans la base de données
        $this->entityManager->persist($user);

        // Sauvegarde les modifications dans la base de données
        $this->entityManager->flush();

        return $user; // Retourne l'utilisateur créé
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Récupère la connexion à la base de données
        $connection = $this->entityManager->getConnection();

        // Désactive les vérifications de clé étrangère pour le nettoyage
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');

        // Récupère les métadonnées des entités pour obtenir les noms de tables
        $metaData = $this->entityManager->getMetadataFactory()->getAllMetadata();

        // Parcourt les métadonnées pour vider les tables
        foreach ($metaData as $classMetaData) {
            if (isset($classMetaData->table['name'])) {
                $tableName = $classMetaData->table['name'];
                $connection->executeStatement("TRUNCATE TABLE `$tableName`"); // Vide la table
            }
        }

        // Réactive les vérifications de clé étrangère
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');

        // Ferme la connexion de l'EntityManager
        $this->entityManager->close();
    }
}
