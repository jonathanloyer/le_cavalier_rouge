<?php

namespace App\Controller;

use App\Form\MatchSheetType;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Repository\ClubRepository;
use App\Repository\CompetitionsRepository;
use App\Repository\FeuilleMatchRepository;
use App\Repository\JoueursRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\PlayerRoleRepository;
use App\Entity\Club;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(
        UserRepository $userRepository,
        ClubRepository $clubRepository,
        CompetitionsRepository $competitionRepository,
        FeuilleMatchRepository $feuilleMatchRepository,
        JoueursRepository $joueurRepository
    ): Response {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_login');
        }

        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_profile');
        }

        $totalUsers = $userRepository->count([]);
        $totalJoueurs = $joueurRepository->count([]);
        $totalClubs = $clubRepository->count([]);
        $activeCompetitions = $competitionRepository->count(['status' => 'active']);

        $matchesThisMonth = $feuilleMatchRepository->createQueryBuilder('f')
            ->select('count(f.id)')
            ->where('f.dateMatch >= :startOfMonth')
            ->setParameter('startOfMonth', new \DateTime('first day of this month'))
            ->getQuery()
            ->getSingleScalarResult();

        $recentActivities = [
            ['message' => 'Nouvel utilisateur inscrit', 'time' => 'il y a 2 heures'],
            ['message' => 'Compétition ajoutée : Open de France', 'time' => 'il y a 1 jour'],
            ['message' => 'Feuille de match soumise', 'time' => 'il y a 3 jours'],
        ];

        return $this->render('pages/admin/index.html.twig', [
            'totalUsers' => $totalUsers,
            'totalJoueurs' => $totalJoueurs,
            'totalClubs' => $totalClubs,
            'activeCompetitions' => $activeCompetitions,
            'matchesThisMonth' => $matchesThisMonth,
            'recentActivities' => $recentActivities,
        ]);
    }

    #[Route('/admin/utilisateurs', name: 'admin_manage_users')]
    public function manageUsers(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->render('pages/admin/manage_users.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/admin/utilisateur/{id}/modifier', name: 'admin_edit_user')]
    public function editUser(
        int $id,
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $userRepository->find($id);
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur modifié avec succès.');
            return $this->redirectToRoute('admin_manage_users');
        }

        return $this->render('pages/admin/edit_user.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/utilisateur/{id}/supprimer', name: 'admin_delete_user', methods: ['POST'])]
    public function deleteUser(int $id, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $userRepository->find($id);
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('success', 'Utilisateur supprimé avec succès.');
        return $this->redirectToRoute('admin_manage_users');
    }

    #[Route('/admin/utilisateur/{id}/ajouter-capitaine', name: 'admin_add_capitaine')]
    public function addCapitaine(int $id, UserRepository $userRepository, ManagerRegistry $doctrine): Response
    {
        $user = $userRepository->find($id);
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }

        $roles = $user->getRoles();
        if (!in_array('ROLE_CAPITAINE', $roles)) {
            $roles[] = 'ROLE_CAPITAINE';
        }
        $user->setRoles($roles);

        $em = $doctrine->getManager();
        $em->persist($user);
        $em->flush();

        $this->addFlash('success', 'Le rôle capitaine a été attribué.');
        return $this->redirectToRoute('admin_manage_users');
    }

    #[Route('/admin/utilisateur/{id}/retirer-capitaine', name: 'admin_remove_capitaine')]
    public function removeCapitaine(int $id, UserRepository $userRepository, ManagerRegistry $doctrine): Response
    {
        $user = $userRepository->find($id);
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }

        $roles = array_diff($user->getRoles(), ['ROLE_CAPITAINE']);
        $user->setRoles($roles);

        $em = $doctrine->getManager();
        $em->persist($user);
        $em->flush();

        $this->addFlash('success', 'Le rôle capitaine a été retiré.');
        return $this->redirectToRoute('admin_manage_users');
    }

    #[Route('/admin/competitions', name: 'admin_manage_competitions')]
    public function manageCompetitions(CompetitionsRepository $competitionRepository): Response
    {
        $competitions = $competitionRepository->findAll();

        return $this->render('pages/admin/manage_competitions.html.twig', [
            'competitions' => $competitions,
        ]);
    }
    #[Route('/admin/joueurs', name: 'admin_manage_players')]
    public function managePlayers(JoueursRepository $joueursRepository): Response
    {
        $joueurs = $joueursRepository->findAll();

        return $this->render('pages/admin/manage_players.html.twig', [
            'joueurs' => $joueurs,
        ]);
    }
    #[Route('/admin/feuilles-de-match', name: 'admin_manage_match_sheets')]
    public function manageMatchSheets(FeuilleMatchRepository $feuilleMatchRepository): Response
    {
        // Récupérer toutes les feuilles de match
        $feuillesMatch = $feuilleMatchRepository->findAll();

        // Retourner la vue pour gérer les feuilles de match
        return $this->render('pages/admin/manage_match_sheets.html.twig', [
            'feuillesMatch' => $feuillesMatch,
        ]);
    }
    #[Route('/admin/clubs', name: 'admin_manage_clubs')]
    public function manageClubs(
        ClubRepository $clubRepository,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        $clubs = $clubRepository->findAll();

        if ($request->isMethod('POST')) {
            $clubName = $request->request->get('name');
            if ($clubName) {
                $club = new Club();
                $club->setName($clubName);

                $entityManager->persist($club);
                $entityManager->flush();

                $this->addFlash('success', 'Le club a été créé avec succès.');
                return $this->redirectToRoute('admin_manage_clubs');
            }

            $this->addFlash('error', 'Le nom du club ne peut pas être vide.');
        }

        return $this->render('pages/admin/manage_clubs.html.twig', [
            'clubs' => $clubs,
        ]);
    }

    #[Route('/admin/clubs/{id}/edit', name: 'admin_edit_club')]
    public function editClub(
        int $id,
        Request $request,
        ClubRepository $clubRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $club = $clubRepository->find($id);

        if (!$club) {
            throw $this->createNotFoundException('Club introuvable.');
        }

        if ($request->isMethod('POST')) {
            $clubName = $request->request->get('name');

            if ($clubName) {
                $club->setName($clubName);
                $entityManager->flush();

                $this->addFlash('success', 'Le club a été mis à jour avec succès.');
                return $this->redirectToRoute('admin_manage_clubs');
            }

            $this->addFlash('error', 'Le nom du club ne peut pas être vide.');
        }

        return $this->render('pages/admin/edit_club.html.twig', [
            'club' => $club,
        ]);
    }

    #[Route('/admin/clubs/{id}/delete', name: 'admin_delete_club', methods: ['POST'])]
    public function deleteClub(int $id, ClubRepository $clubRepository, EntityManagerInterface $entityManager): Response
    {
        $club = $clubRepository->find($id);

        if ($club) {
            $entityManager->remove($club);
            $entityManager->flush();

            $this->addFlash('success', 'Le club a été supprimé avec succès.');
        } else {
            $this->addFlash('error', 'Le club n\'existe pas.');
        }

        return $this->redirectToRoute('admin_manage_clubs');
    }

    #[Route('/admin/parametres', name: 'admin_settings')]
    public function settings(Request $request): Response
    {
        // Vous pouvez ajouter une logique spécifique pour gérer les paramètres ici
        // Par exemple, charger des configurations ou traiter un formulaire

        return $this->render('pages/admin/settings.html.twig');
    }

    #[Route('/admin/joueurs/actifs', name: 'admin_active_players')]
    public function manageActivePlayers(UserRepository $userRepository): Response
    {
        // Récupère uniquement les utilisateurs actifs
        $activePlayers = $userRepository->findBy(['active' => true]);

        // Débogage temporaire
        if (empty($activePlayers)) {
            throw new \Exception('Aucun utilisateur actif trouvé. Vérifiez les données dans la base de données.');
        }

        return $this->render('pages/admin/manage_active_players.html.twig', [
            'joueurs' => $activePlayers,
        ]);
    }
}
