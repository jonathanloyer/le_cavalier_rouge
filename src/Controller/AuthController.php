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
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class AuthController extends AbstractController
{
    #[Route('/inscription', name: 'app_inscription')]
    public function inscription(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $newuser = new User();
        $form = $this->createForm(InscriptionType::class, $newuser);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // je vérifie si un utilisateur avec son email existe déjà
            $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $newuser->getEmail()]);
            if ($existingUser) {
                $this->addFlash('error', 'Un compte avec cet email existe déjà.');
                return $this->redirectToRoute('app_inscription');
            }

            // je hashe le mot de passe pour une meilleur sécurité
            $hashedPassword = $passwordHasher->hashPassword($newuser, $newuser->getPassword());
            $newuser->setPassword($hashedPassword);

            // j'enregistre l'utilisateur dans la base de données en utilisant la function persist et flush
            $entityManager->persist($newuser);
            $entityManager->flush();

            // Message d'incription si cela fonctionne
            $this->addFlash('success', 'Inscription réussie ! Vous pouvez maintenant vous connecter.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('pages/auth/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    // pour la function deco pas besoin d'en faire plus car symfony gérera automatiquement 
    #[Route('/deconnexion', name: 'app_logout', methods: ['GET'])]
    public function deco(): void
    {
       
    }

}
