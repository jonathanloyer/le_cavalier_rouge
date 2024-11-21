<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
public function login(AuthenticationUtils $authenticationUtils):Response
{
    // je test les erreurs de connexion
    $error = $authenticationUtils->getLastAuthenticationError();

    // je récupère le dernier email entré par l'utilisateur
    $lastUsername = $authenticationUtils->getLastUsername();

    // je retourne la vue de connexion
    return $this->render('pages/login/index.html.twig', [
        'last_username' => $lastUsername,
        'error' => $error,
    ]);
}
}