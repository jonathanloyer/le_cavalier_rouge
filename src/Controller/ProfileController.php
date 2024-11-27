<?php 

namespace App\Controller;

use App\Form\ProfileType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
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
    public function profile (
        Request $request,
        UserRepository $repo,
        SluggerInterface $slugger,
        #[Autowire('%kernel.project_dir%/public/uploads/avatar')] string $avatarDirectory): Response
        {
         $user=$repo->findOneBy(['email'=>$this->getUser()->getUserIdentifier()]);
            $form=$this->createForm(ProfileType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $avatarFile = $form->get('avatar')->getData();

                if ($avatarFile) {
                    $originalFilename = pathinfo($avatarFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFileName = $slugger->slug($originalFilename);
                    $newFileName = $safeFileName.'-'.uniqid().'.'.$avatarFile->guessExtension();

                    try {
                        $avatarFile->move($avatarDirectory, $newFileName);
                    } catch (FileException $e) {
                        $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi de votre avatar');
                    }
                    $user->setAvatar($newFileName);
                }
                return $this->redirectToRoute('app_login');
            }
            return $this->render('pages/profile/index.html.twig', [
                'user' => $user,
            ]);
        }
        // fonction permettant de modifier le profil de l'utilisateur
        #[Route('/profile/update', name: 'app_profile_update')]
        public function updateProfile(
            Request $request,
            UserRepository $repo,
            SluggerInterface $slugger,
            // Injection de dépendance pour le répertoire des avatars
            #[Autowire('%kernel.project_dir%/public/uploads/avatar')] string $avatarDirectory): Response
            {
                // Récupérer l'utilisateur connecté par son email
                $user=$repo->findOneBy(['email'=>$this->getUser()->getUserIdentifier()]);
                // Créer le formulaire de modification du profil
                $form=$this->createForm(ProfileType::class, $user);
                // Gérer la requête
                $form->handleRequest($request);


                // Si le formulaire est soumis et valide
                if ($form->isSubmitted() && $form->isValid()) {

                    // Récupérer le fichier de l'avatar
                    $avatarFile = $form->get('avatar')->getData();

                    // Si un fichier est envoyé
                    if ($avatarFile) {

                        // Générer un nom de fichier sécurisé
                        $originalFilename = pathinfo($avatarFile->getClientOriginalName(), PATHINFO_FILENAME);

                        // Utiliser le service Slugger pour générer un slug
                        $safeFileName = $slugger->slug($originalFilename);
                        // Ajouter un identifiant unique au nom du fichier
                        $newFileName = $safeFileName.'-'.uniqid().'.'.$avatarFile->guessExtension();
    
                        // Déplacer le fichier dans le répertoire des avatars
                        try {
                            $avatarFile->move($avatarDirectory, $newFileName);
                            // Supprimer l'ancien avatar
                        } catch (FileException $e) {
                            // Gérer les erreurs
                            $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi de votre avatar');
                        }

                        // Mettre à jour le nom du fichier dans la base de données
                        $user->setAvatar($newFileName);
                        // Sauvegarder les modifications
                        $repo->save($user);
                        // Ajouter un message de succès
                        $this->addFlash('success','Votre profil a bien été mis à jour');
                        
                    }
                    // Rediriger l'utilisateur vers la page de profil
                    return $this->redirectToRoute('app_profile');
                }
                // Afficher le formulaire modifié
                return $this->render('pages/profile/update_profile.html.twig', [
                    // passer le formulaire à la vue
                    'form'=> $form->createView(), 
                    // passer l'utilisateur connecté à la vue
                    'user' => $user,
                ]);
            }        
    }


