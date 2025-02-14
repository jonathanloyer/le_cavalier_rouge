<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ContactControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testPublicContactWithEmptyFields(): void
    {
        $crawler = $this->client->request('GET', '/contactez-nous');

        $form = $crawler->selectButton('Envoyer')->form([
            'contact[name]' => '',
            'contact[firstname]' => '',
            'contact[email]' => '',
            'contact[message]' => ''
        ]);

        $this->client->submit($form);

        // Vérification de la présence des erreurs dans le formulaire
        $this->assertSelectorTextContains('form', 'Le nom est requis');
        $this->assertSelectorTextContains('form', 'Le prénom est requis');
        $this->assertSelectorTextContains('form', "L'email est requis");
        $this->assertSelectorTextContains('form', 'Le message est requis');
    }

    public function testPublicContactWithValidData(): void
    {
        $crawler = $this->client->request('GET', '/contactez-nous');

        $form = $crawler->selectButton('Envoyer')->form([
            'contact[name]' => 'John',
            'contact[firstname]' => 'Doe',
            'contact[email]' => 'john.doe@example.com',
            'contact[message]' => 'Ceci est un message de test.'
        ]);

        $this->client->submit($form);

        // Vérification de la redirection après soumission (vers la page d'accueil)
        $this->assertResponseRedirects($this->client->getContainer()->get('router')->generate('app_home'));

        // Suivre la redirection et vérifier la présence du message de succès
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.flash-success', 'Votre message a été envoyé avec succès.');
    }
}
