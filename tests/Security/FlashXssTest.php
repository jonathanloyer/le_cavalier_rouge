<?php

namespace App\Tests\Security;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\Session\Session;

class FlashXssTest extends WebTestCase
{
    public function testFlashMessageXssProtection()
    {
        $client = static::createClient();

        // Créer une session factice pour le test
        $session = new Session(new MockArraySessionStorage());
        $session->start();
        $session->getFlashBag()->add('success', '<script>alert("XSS")</script>');

        // Sauvegarder la session et attacher la session au client
        $client->getCookieJar()->set(new \Symfony\Component\BrowserKit\Cookie(session_name(), $session->getId()));

        // Accéder à la page où les messages flashs sont affichés
        $crawler = $client->request('GET', '/page-qui-affiche-des-flashs');

        // Vérifier que le message XSS est bien affiché sous forme échappée
        $this->assertStringContainsString('&lt;script&gt;alert("XSS")&lt;/script&gt;', $client->getResponse()->getContent());
    }
}
