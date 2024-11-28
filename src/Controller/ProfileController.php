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
                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi de votre avatar');
                    return $this->redirectToRoute('app_profile');
                }

                // Mettre à jour le chemin du nouvel avatar dans l'utilisateur
                $user->setAvatar($newFileName);
            }

            // Sauvegarder l'utilisateur
            $repo->save($user, true);

            return $this->redirectToRoute('app_profile');
        }

        return $this->render('pages/profile/index.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    // Fonction permettant de modifier le profil de l'utilisateur
    #[Route('/profile/update', name: 'app_profile_update')]
    public function updateProfile(
        Request $request,
        UserRepository $repo,
        SluggerInterface $slugger,
        #[Autowire('%kernel.project_dir%/public/uploads/avatar')] string $avatarDirectory
    ): Response {
        $user = $repo->findOneBy(['email' => $this->getUser()->getUserIdentifier()]);
        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer le fichier de l'avatar
            $avatarFile = $form->get('avatar')->getData();

            if ($avatarFile) {
                // Générer un nom de fichier sécurisé
                $originalFilename = pathinfo($avatarFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFileName = $slugger->slug($originalFilename);
                $newFileName = $safeFileName . '-' . uniqid() . '.' . $avatarFile->guessExtension();

                // Déplacer le fichier dans le répertoire des avatars
                try {
                    $avatarFile->move($avatarDirectory, $newFileName);

                    // Supprimer l'ancien avatar si nécessaire
                    if ($user->getAvatar()) {
                        $oldAvatarPath = $avatarDirectory . '/' . $user->getAvatar();
                        if (file_exists($oldAvatarPath)) {
                            unlink($oldAvatarPath);  // Supprime l'ancien avatar
                        }
                    }

                    // Mettre à jour l'avatar dans l'entité User
                    $user->setAvatar($newFileName);
                } catch (FileException $e) {
                    // Gérer les erreurs de fichier
                    $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi de votre avatar');
                    return $this->redirectToRoute('app_profile_update');
                }
            }

            // Sauvegarder les modifications
            $repo->save($user, true);

            // Ajouter un message de succès
            $this->addFlash('success', 'Votre profil a bien été mis à jour');

            // Rediriger l'utilisateur vers la page de profil
            return $this->redirectToRoute('app_profile');
        }

        // Afficher le formulaire de mise à jour
        return $this->render('pages/profile/update_profile.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }
}
