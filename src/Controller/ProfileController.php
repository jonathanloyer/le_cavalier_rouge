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
        #[Route('/profile/update', name: 'app_profile_update')]
        public function updateProfile(
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
                        // Sauvegarder les modifications
                        $repo->save($user);
                        $this->addFlash('success','Votre profil a bien été mis à jour');
                        
                    }
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


