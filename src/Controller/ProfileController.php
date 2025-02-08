<?php

namespace App\Controller;

use App\Form\ProfileType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function profile(
        Request $request,
        UserRepository $repo,
        SluggerInterface $slugger,
        #[Autowire('%kernel.project_dir%/public/uploads/avatar')] string $avatarDirectory
    ): Response {
        $user = $repo->findOneBy(['email' => $this->getUser()->getUserIdentifier()]);
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }

        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $avatarFile = $form->get('avatar')->getData();

            if ($avatarFile) {
                $originalFilename = pathinfo($avatarFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFileName = $slugger->slug($originalFilename);
                $newFileName = $safeFileName . '-' . uniqid() . '.' . $avatarFile->guessExtension();

                try {
                    $avatarFile->move($avatarDirectory, $newFileName);

                    // Supprimer l'ancien avatar s'il existe
                    if ($user->getAvatar()) {
                        $oldAvatarPath = $avatarDirectory . '/' . $user->getAvatar();
                        if (file_exists($oldAvatarPath)) {
                            unlink($oldAvatarPath);
                        }
                    }

                    // Mettre à jour l'avatar de l'utilisateur
                    $user->setAvatar($newFileName);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi de votre avatar.');
                    return $this->redirectToRoute('app_profile');
                }
            }

            // Sauvegarder les modifications
            $repo->save($user, true);

            $this->addFlash('success', 'Votre profil a bien été mis à jour.');
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('pages/profile/index.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/profile/update', name: 'app_profile_update')]
    public function updateProfile(
        Request $request, // la requête HTTP
        UserRepository $repo, // le dépôt UserRepository
        SluggerInterface $slugger, // le service SluggerInterface qui sert à générer des slugs pour les noms de fichiers
        #[Autowire('%kernel.project_dir%/public/uploads/avatar')] string $avatarDirectory // le répertoire où les avatars seront stockés
    ): Response {

        // Récupérer l'utilisateur connecté
        $user = $repo->findOneBy(['email' => $this->getUser()->getUserIdentifier()]);

        // Vérifier si l'utilisateur existe
        if (!$user) {

            // Si l'utilisateur n'existe pas, on lance une exception
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }

        // Créer un formulaire pour mettre à jour le profil de l'utilisateur
        $form = $this->createForm(ProfileType::class, $user);

        // Traiter la requête
        $form->handleRequest($request);

        // Vérifier si le formulaire a été soumis et est valide
        if ($form->isSubmitted() && $form->isValid()) {
            $avatarFile = $form->get('avatar')->getData();  // Récupérer le fichier avatar

            // Si un fichier avatar a été envoyé
            if ($avatarFile) {

                // Générer un nom de fichier sécurisé
                $originalFilename = pathinfo($avatarFile->getClientOriginalName(), PATHINFO_FILENAME); // Récupérer le nom du fichier le pathinfo sert à récupérer des informations sur le chemin d'accès à un fichier qui est passé en argument le getOriginalName sert à récupérer le nom original du fichier

                $safeFileName = $slugger->slug($originalFilename); // Générer un slug à partir du nom du fichier
                $newFileName = $safeFileName . '-' . uniqid() . '.' . $avatarFile->guessExtension(); // Générer un nom de fichier unique l'ununiqid() génère un identifiant unique basé sur la date courante en microsecondes et le guessExtension() permet de deviner l'extension du fichier en fonction de son contenu puis je l'ai concaténé avec le nom du fichier car le nom du fichier ne contient pas l'extension

                // Déplacer le fichier avatar vers le répertoire de stockage
                try {

                    $avatarFile->move($avatarDirectory, $newFileName); // car le fichier est stocké dans le répertoire de stockage

                    // Supprimer l'ancien avatar s'il existe
                    if ($user->getAvatar()) {
                        $oldAvatarPath = $avatarDirectory . '/' . $user->getAvatar(); // Récupérer le chemin de l'ancien avatar
                        if (file_exists($oldAvatarPath)) { // Vérifier si le fichier existe

                            unlink($oldAvatarPath); // Supprimer l'ancien avatar, le unling() sert à supprimer un fichier
                        }
                    }

                    // Mettre à jour l'avatar de l'utilisateur
                    $user->setAvatar($newFileName);

                    // Si une erreur survient lors de l'envoi du fichier
                } catch (FileException $e) {

                    // Afficher un message d'erreur
                    $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi de votre avatar.');

                    // Rediriger l'utilisateur vers la page de mise à jour du profil
                    return $this->redirectToRoute('app_profile_update');
                }
            }

            // Sauvegarder les modifications
            $repo->save($user, true);

            $this->addFlash('success', 'Votre profil a bien été mis à jour.');
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('pages/profile/update_profile.html.twig', [ // Afficher le formulaire de mise à jour du profil
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
    #[Route('/delete-account', name: 'app_delete_account', methods: ['POST'])]
    public function deleteAccount(EntityManagerInterface $entityManager, Request $request): Response
    {
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour supprimer votre compte.');
            return $this->redirectToRoute('app_login');
        }

        // Vérifier le token CSRF
        if (!$this->isCsrfTokenValid('delete_account', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_profile');
        }

        // Supprimer l'utilisateur de la base de données
        $entityManager->remove($user);
        $entityManager->flush();

        // Déconnecter l'utilisateur après la suppression
        $this->container->get('security.token_storage')->setToken(null);
        $request->getSession()->invalidate();

        $this->addFlash('success', 'Votre compte a été supprimé avec succès.');

        return $this->redirectToRoute('app_home'); // Redirection vers la page d'accueil
    }
}
