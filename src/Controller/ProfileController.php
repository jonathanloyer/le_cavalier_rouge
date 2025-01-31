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
                    return $this->redirectToRoute('app_profile_update');
                }
            }

            // Sauvegarder les modifications
            $repo->save($user, true);

            $this->addFlash('success', 'Votre profil a bien été mis à jour.');
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('pages/profile/update_profile.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}
