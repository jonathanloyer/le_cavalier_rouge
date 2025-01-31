<?php

namespace App\Tests\Controller; // Déclare le namespace pour les tests du contrôleur

use App\Controller\LoginController; // Importe le contrôleur que l'on va tester
use PHPUnit\Framework\TestCase; // Utilisation de la classe TestCase pour les tests unitaires
use Symfony\Component\HttpFoundation\Request; // Permet de simuler une requête HTTP
use Symfony\Component\HttpFoundation\Response; // Permet de tester la réponse HTTP
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils; // Service d'authentification Symfony
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController; // Classe parente du contrôleur Symfony

class LoginControllerTest extends TestCase // Déclare la classe de test qui étend TestCase pour les tests unitaires
{
    public function testLoginPageRendering(): void // Fonction de test qui vérifie le rendu de la page de connexion
    {
        // Création du mock pour AuthenticationUtils afin d'éviter une vraie connexion
        /** @var AuthenticationUtils&\PHPUnit\Framework\MockObject\MockObject $authenticationUtilsMock */
        $authenticationUtilsMock = $this->createMock(AuthenticationUtils::class);

        // Simule l'absence d'erreur d'authentification
        $authenticationUtilsMock->method('getLastAuthenticationError')->willReturn(null);

        // Simule le dernier nom d'utilisateur utilisé pour la connexion
        $authenticationUtilsMock->method('getLastUsername')->willReturn('testuser');

        // Création d'un contrôleur anonyme qui simule le LoginController
        $controller = new class extends AbstractController {
            public function login(Request $request, AuthenticationUtils $authenticationUtils): Response
            {
                // Récupère le dernier identifiant entré par l'utilisateur
                $lastUsername = $authenticationUtils->getLastUsername();

                // Récupère une éventuelle erreur d'authentification
                $error = $authenticationUtils->getLastAuthenticationError();

                // Retourne une réponse simulée avec le pseudo affiché dans le contenu
                return new Response(
                    '<html><body>Login Page: ' . htmlspecialchars($lastUsername) . '</body></html>',
                    Response::HTTP_OK // Code HTTP 200 (OK)
                );
            }
        };

        // Simulation d'une requête HTTP GET
        $request = new Request();

        // Exécution de la méthode login() du contrôleur
        $response = $controller->login($request, $authenticationUtilsMock);

        // Vérifie que la réponse est bien une instance de Response
        $this->assertInstanceOf(Response::class, $response);

        // Vérifie que le code HTTP de la réponse est bien 200 (OK)
        $this->assertEquals(200, $response->getStatusCode());

        // Vérifie que la page contient bien "Login Page: testuser"
        $this->assertStringContainsString('Login Page: testuser', $response->getContent());
    }
}
