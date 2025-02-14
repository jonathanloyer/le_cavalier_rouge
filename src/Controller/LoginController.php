<?php

namespace App\Controller;

use App\Form\LoginType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
public function login(Request $request, AuthenticationUtils $authenticationUtils):Response
{
    if ($this->getUser()) {  // Vérifier si l'utilisateur est déjà authentifié
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_admin'); // Rediriger les admins vers le tableau de bord
        }

        if ($this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('app_profile'); // Rediriger les utilisateurs vers leur profil
        }
    }
    // je test les erreurs de connexion
    $error = $authenticationUtils->getLastAuthenticationError();

    // je récupère le dernier email entré par l'utilisateur
    $lastUsername = $authenticationUtils->getLastUsername();

    $form= $this->createForm(LoginType::class );
    // je retourne la vue de connexion
    return $this->render('pages/login/index.html.twig', [
        'last_username' => $lastUsername,
        'error' => $error,
        'form' => $form->createView()
        
    ]);
}
}