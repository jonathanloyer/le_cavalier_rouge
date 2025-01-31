<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class HomeControllerTest extends WebTestCase
{
    public function testHomePage(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        // Vérifier que la réponse est bien 200 OK
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        // Vérifier que la page contient bien un titre h1
        $this->assertSelectorExists('h1');

        // Vérifier que le h1 contient bien le texte attendu
        $this->assertSelectorTextContains('h1', 'Votre association à Clamart');
    }
}
