<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\InscriptionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController
{
    #[Route('/inscription', name: 'app_inscription')]
    public function inscription(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $newuser = new User(); // Création d’un nouvel utilisateur
        $form = $this->createForm(InscriptionType::class, $newuser); // Création du formulaire

        $form->handleRequest($request); // Traitement de la requête

        if ($form->isSubmitted() && $form->isValid()) { // Si le formulaire est soumis et valide
            // Hashage du mot de passe
            $hashedPassword = $passwordHasher->hashPassword($newuser, $form->get('password')->get('first')->getData()); // Récupération du mot de passe
            $newuser->setPassword($hashedPassword); // Définition du mot de passe hashé
            $newuser->setRoles(['ROLE_USER']); // Attribution du rôle ROLE_USER
            $newuser->setActive(true); // Activation du compte

            // Sauvegarde en base
            $entityManager->persist($newuser); // Préparation de la sauvegarde
            $entityManager->flush(); // Sauvegarde

            // Redirection vers la page de connexion
            $this->addFlash('success', 'Inscription réussie ! Vous pouvez maintenant vous connecter.');
            return $this->redirectToRoute('app_login'); 
        }
        
        return $this->render('pages/auth/index.html.twig', [
            'form' => $form->createView(), // Transmission du formulaire à la vue
        ]);
    }

    #[Route('/deconnexion', name: 'app_logout', methods: ['GET'])]
    public function deco(): Response
    {
        return $this->redirectToRoute('app_home');
    }
}
