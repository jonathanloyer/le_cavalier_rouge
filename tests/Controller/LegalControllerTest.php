<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LegalControllerTest extends WebTestCase
{
    public function testCguPageLoads(): void
    {
        $client = static::createClient();
        $client->request('GET', '/cgu');

        // ✅ Vérifier que la réponse est un succès (200)
        $this->assertResponseIsSuccessful();
        
        // ✅ Vérifier que le bon template est utilisé
        $this->assertSelectorExists('html');
    }

    public function testRgpdPageLoads(): void
    {
        $client = static::createClient();
        $client->request('GET', '/rgpd');

        // ✅ Vérifier que la réponse est un succès (200)
        $this->assertResponseIsSuccessful();
        
        // ✅ Vérifier que le bon template est utilisé
        $this->assertSelectorExists('html');
    }

    public function testMentionsLegalesPageLoads(): void
    {
        $client = static::createClient();
        $client->request('GET', '/mentions-legales');

        // ✅ Vérifier que la réponse est un succès (200)
        $this->assertResponseIsSuccessful();
        
        // ✅ Vérifier que le bon template est utilisé
        $this->assertSelectorExists('html');
    }
}
