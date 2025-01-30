<?php

namespace App\Tests\Controller;

use App\Entity\FeuilleMatch;
use App\Entity\User;
use App\Repository\FeuilleMatchRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase; 
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class MatchSheetsControllerTest extends WebTestCase 
{
    private function loginUser($client)
    {
        // Récupération correcte du repository
        $userRepository = static::getContainer()->get(UserRepository::class);
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $testUser = $userRepository->findOneBy(['pseudo' => 'admin']);

        if (!$testUser) {
            $testUser = new User();
            $testUser->setEmail('admin@example.com')
                ->setPseudo('admin')
                ->setFirstName('Admin')
                ->setLastName('User')
                ->setPassword($passwordHasher->hashPassword($testUser, 'admin123'))
                ->setRoles(['ROLE_ADMIN'])
                ->setActive(true);

            $entityManager->persist($testUser);
            $entityManager->flush();
        }

        $client->loginUser($testUser);
    }

    public function testIndex()
    {
        $client = static::createClient();
        $this->loginUser($client);

        $client->request('GET', '/feuille-de-match');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('html');
    }

    public function testShowWithInvalidId()
    {
        $client = static::createClient();
        $this->loginUser($client);

        $client->request('GET', '/feuille-de-match/999');

        $this->assertResponseRedirects('/feuille-de-match/consulter', Response::HTTP_FOUND);

        $crawler = $client->followRedirect();
        $this->assertSelectorExists('.alert-danger');
        $this->assertSelectorTextContains('.alert-danger', 'Feuille de match introuvable.');
    }

    public function testDelete()
    {
        static::ensureKernelShutdown();
        $client = static::createClient();
        $this->loginUser($client);

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $feuilleMatchRepository = static::getContainer()->get(FeuilleMatchRepository::class);

        $feuilleMatch = $feuilleMatchRepository->findOneBy([]);
        if (!$feuilleMatch) {
            $feuilleMatch = new FeuilleMatch();
            $feuilleMatch->setDateMatch(new \DateTimeImmutable());
            $feuilleMatch->setCreation(new \DateTimeImmutable());
            $feuilleMatch->setRonde(1); // 🔥 Ajout d'une ronde
            $feuilleMatch->setType('standard'); // 🔥 Ajout d'un type
            $feuilleMatch->setGroupe('Groupe A'); // 🔥 Ajout du groupe
            $feuilleMatch->setInterclub('Interclub Régional'); // 🔥 Ajout de l'interclub
            $entityManager->persist($feuilleMatch);
            $entityManager->flush();
        }

        $client->request('POST', '/feuille-de-match/' . $feuilleMatch->getId() . '/supprimer');

        $this->assertResponseRedirects('/feuille-de-match/consulter', Response::HTTP_FOUND);
    }

    public function testEditWithValidData()
    {
        static::ensureKernelShutdown();
        $client = static::createClient();
        $this->loginUser($client);

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $feuilleMatchRepository = static::getContainer()->get(FeuilleMatchRepository::class);

        $feuilleMatch = $feuilleMatchRepository->findOneBy([]);
        if (!$feuilleMatch) {
            $feuilleMatch = new FeuilleMatch();
            $feuilleMatch->setDateMatch(new \DateTimeImmutable());
            $feuilleMatch->setCreation(new \DateTimeImmutable());
            $feuilleMatch->setRonde(1); // 🔥 Ajout d'une ronde
            $feuilleMatch->setType('standard'); // 🔥 Ajout d'un type
            $feuilleMatch->setGroupe('Groupe A'); // 🔥 Ajout du groupe
            $feuilleMatch->setInterclub('Interclub Régional'); // 🔥 Ajout de l'interclub
            $entityManager->persist($feuilleMatch);
            $entityManager->flush();
        }

        $client->request('POST', '/feuille-de-match/' . $feuilleMatch->getId() . '/modifier', [
            'clubA' => 1,
            'clubB' => 2,
            'division' => 'criterium',
            'groupe' => 'Groupe A', // 🔥 Correction ici : ajout du groupe
            'interclub' => 'Interclub Régional', // 🔥 Correction ici : ajout de l'interclub
            'dateMatch' => '2024-02-01',
            'joueursA' => [1, 2, 3],
            'joueursB' => [4, 5, 6],
            'resultats' => ['1-0', '0-1', '1/2-1/2'],
            'capitaineA' => 1,
            'capitaineB' => 2,
            'arbitre' => 3,
        ]);

        $this->assertResponseRedirects('/feuille-de-match/consulter', Response::HTTP_FOUND);
    }
}
