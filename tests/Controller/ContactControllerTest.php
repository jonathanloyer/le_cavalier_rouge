<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ContactControllerTest extends WebTestCase
{
    /**
     * Teste si la page de contact se charge correctement.
     */
    public function testPublicContactPageLoads(): void
    {
        // Crée un client HTTP pour simuler un navigateur
        $client = static::createClient();

        // Effectue une requête GET sur la page de contact
        $crawler = $client->request('GET', '/contactez-nous');

        // Vérifie que la réponse HTTP est un succès (code 200)
        $this->assertResponseIsSuccessful();
        
        // Vérifie que le titre <h1> de la page est bien "Contactez-nous"
        $this->assertSelectorTextContains('h3', 'Contactez-nous');
    }

    /**
     * Teste l'envoi du formulaire avec des champs vides.
     */
    public function testPublicContactWithEmptyFields(): void
    {
        // Crée un client HTTP
        $client = static::createClient();
        
        // Envoie une requête POST sans aucune donnée
        $crawler = $client->request('POST', '/contactez-nous', []);

        // Vérifie que la page retourne bien une réponse HTML valide (pas d'erreur 500)
        $this->assertResponseIsSuccessful();

        // Vérifie que le message d'erreur "Tous les champs doivent être remplis." apparaît bien
        $this->assertSelectorTextContains('.flash-error', 'Tous les champs doivent être remplis.');
    }

    /**
     * Teste l'envoi du formulaire avec des données valides.
     */
    public function testPublicContactWithValidData(): void
    {
        // Crée un client HTTP
        $client = static::createClient();
        
        // Envoie une requête POST avec des données valides
        $client->request('POST', '/contactez-nous', [
            'name' => 'Jonathan Loyer',
            'email' => 'jonathan@example.com',
            'message' => 'Test message'
        ]);

        // Vérifie que la réponse est une redirection vers le formulaire après soumission
        $this->assertResponseRedirects('/contactez-nous');
        
        // Suit la redirection pour afficher la page après soumission
        $client->followRedirect();

        // Vérifie que le message de succès est bien affiché
        $this->assertSelectorTextContains('.flash-success', 'Votre message a été envoyé avec succès.');
    }
}
