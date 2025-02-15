<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ContactControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
    }

    public function testPublicContactWithEmptyFields(): void
{
    // Accéder à la page de contact
    $crawler = $this->client->request('GET', '/contactez-nous');

    // Sélectionner et soumettre le formulaire avec des champs vides
    $form = $crawler->selectButton('Envoyer')->form([
        'contact[name]' => '',
        'contact[firstname]' => '',
        'contact[email]' => '',
        'contact[message]' => ''
    ]);

    $this->client->submit($form);

    // ✅ Debug : Voir le contenu HTML après soumission
    file_put_contents('test-output.html', $this->client->getResponse()->getContent());

    // Vérifier que la réponse est OK (200)
    $this->assertResponseIsSuccessful();
}

    public function testPublicContactWithValidData(): void
    {
        // Accéder à la page de contact
        $crawler = $this->client->request('GET', '/contactez-nous');

        // Sélectionner et remplir le formulaire
        $form = $crawler->selectButton('Envoyer')->form([
            'contact[name]' => 'John',
            'contact[firstname]' => 'Doe',
            'contact[email]' => 'john.doe@example.com',
            'contact[message]' => 'Ceci est un message de test.'
        ]);

        $this->client->submit($form);

        // Vérifier que la soumission redirige bien vers la page d'accueil
        $this->assertResponseRedirects('/');

        // Suivre la redirection et vérifier la présence du message de succès
        $this->client->followRedirect();
        $this->assertSelectorExists('.flash-success', 'Le message de confirmation doit être affiché.');
        $this->assertSelectorTextContains('.flash-success', 'Votre message a été envoyé avec succès.');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // Si besoin, nettoyer la base après chaque test (ex: suppression des messages de contact)
    }
}
