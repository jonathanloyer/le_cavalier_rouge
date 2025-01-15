<?php

namespace App\Controller;

use App\Form\MatchSheetType;
use App\Repository\UserRepository;
use App\Repository\ClubRepository;
use App\Repository\CompetitionsRepository;
use App\Repository\FeuilleMatchRepository;
use App\Repository\JoueursRepository;
use App\Repository\PlayerRoleRepository;
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
        // Vérification des permissions
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_login');
        }

        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_profile');
        }

        // Données dynamiques pour le tableau de bord

        // Compte le nombre total d'utilisateurs dans la base de données en utilisant le UserRepository
        $totalUsers = $userRepository->count([]);

        // compte le nombre total de joueurs dans la base de données en utilisant le JoueurRepository
        $totalJoueurs = $joueurRepository->count([]);

        // Compte le nombre total de clubs dans la base de données en utilisant le ClubRepository
        $totalClubs = $clubRepository->count([]);

        // Compte le nombre total de compétitions actives (avec le statut 'active') en utilisant le CompetitionRepository
        $activeCompetitions = $competitionRepository->count(['status' => 'active']);

        // Compte le nombre de feuilles de match créées depuis le début du mois en utilisant une requête personnalisée
        $matchesThisMonth = $feuilleMatchRepository->createQueryBuilder('f') // Crée un QueryBuilder pour l'entité "Feuille de match"

            ->select('count(f.id)') // Sélectionne le nombre total d'ID dans la table des feuilles de match

            ->where('f.dateMatch >= :startOfMonth') // Ajoute une condition pour inclure uniquement les feuilles de match à partir du début du mois

            ->setParameter('startOfMonth', new \DateTime('first day of this month')) // Définit le paramètre :startOfMonth à la première date du mois actuel

            ->getQuery() // Génère la requête basée sur les critères définis

            ->getSingleScalarResult();  // Récupère le résultat unique (le nombre de feuilles de match)

        // Définit un tableau d'activités récentes à afficher sur le tableau de bord
        $recentActivities = [
            ['message' => 'Nouvel utilisateur inscrit', 'time' => 'il y a 2 heures'],  // Récupère le résultat unique (le nombre de feuilles de match)

            ['message' => 'Compétition ajoutée : Open de France', 'time' => 'il y a 1 jour'], // Exemple d'une compétition ajoutée récemment

            ['message' => 'Feuille de match soumise', 'time' => 'il y a 3 jours'], // Exemple d'une soumission récente de feuille de match
        ];

        // Rend la vue Twig pour la page d'administration en passant les données calculées à la vue
        return $this->render('pages/admin/index.html.twig', [
            'totalUsers' => $totalUsers, // Passe le nombre total d'utilisateurs à la vue
            'totalJoueurs' => $totalJoueurs, // Passe le nombre total de joueurs à la vue
            'totalClubs' => $totalClubs, // Passe le nombre total de clubs à la vue
            'activeCompetitions' => $activeCompetitions, // Passe le nombre total de compétitions actives à la vue
            'matchesThisMonth' => $matchesThisMonth, // Passe le nombre de matchs du mois à la vue
            'recentActivities' => $recentActivities, // Passe les activités récentes à la vue
        ]);
    }
    // fonction permettant de gérer les utilisateurs
    #[Route('/admin/utilisateurs', name: 'admin_manage_users')]
    public function manageUsers(UserRepository $userRepository): Response
    {
        // Récupère la liste de tous les utilisateurs en utilisant le UserRepository
        $users = $userRepository->findAll();

        return $this->render('pages/admin/manage_users.html.twig', [
            'users' => $users,
        ]);
    }
    // Route pour modifier un utilisateur
    #[Route('/admin/utilisateur/{id}/modifier', name: 'admin_edit_user')]
    public function editUser(int $id, UserRepository $userRepository): Response
    {
        $user = $userRepository->find($id);
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        // Ici tu peux traiter les données du formulaire de modification

        // Pour l'instant, redirige vers la page de gestion des utilisateurs
        return $this->redirectToRoute('admin_manage_users');
    }

    // Fonction pour supprimer un utilisateur
    #[Route('/admin/utilisateur/{id}/supprimer', name: 'admin_delete_user', methods: ['POST'])]
    public function deleteUser(int $id, UserRepository $userRepository): Response
    {
        $user = $userRepository->find($id);
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        $entityManager = $this->$this->getDoctrine()->getManager();
        $entityManager->remove($user);
        $entityManager->flush();

        // Rediriger après suppression
        return $this->redirectToRoute('admin_manage_users');
    }

    #[Route('/admin/clubs', name: 'admin_manage_clubs')]
    public function manageClubs(ClubRepository $clubRepository): Response
    {
        $clubs = $clubRepository->findAll();

        return $this->render('pages/admin/manage_clubs.html.twig', [
            'clubs' => $clubs,
        ]);
    }

    #[Route('/admin/competitions', name: 'admin_manage_competitions')]
    public function manageCompetitions(CompetitionsRepository $competitionRepository): Response
    {
        $competitions = $competitionRepository->findAll();

        return $this->render('pages/admin/manage_competitions.html.twig', [
            'competitions' => $competitions,
        ]);
    }

    #[Route('/admin/feuilles-match', name: 'admin_manage_match_sheets')]
public function manageMatchSheets(Request $request, FeuilleMatchRepository $feuilleMatchRepository): Response
{
    // Création du formulaire
    $form = $this->createForm(MatchSheetType::class);

    // Gestion de la soumission du formulaire
    $form->handleRequest($request);

    // Vérifie si le formulaire a été soumis et est valide
    if ($form->isSubmitted() && $form->isValid()) {
        // Récupère les données du formulaire
        $data = $form->getData();

        // Vérifie si l'une des actions de bouton a été soumise en comparant les données
        if (isset($data['creer'])) {
            // Si le bouton "Créer" a été cliqué
            return $this->redirectToRoute('app_create_match_sheet', ['type' => $data['typeFeuille']]);
        }

        if (isset($data['consulter'])) {
            // Si le bouton "Consulter" a été cliqué
            return $this->redirectToRoute('app_view_match_sheet', ['reference' => $data['reference']]);
        }
    }

    // Récupère toutes les feuilles de match pour les afficher
    $feuillesMatch = $feuilleMatchRepository->findAll();

    // Rend la vue avec les données
    return $this->render('pages/admin/manage_match_sheets.html.twig', [
        'feuillesMatch' => $feuillesMatch,
        'form' => $form->createView(),
    ]);
}


    #[Route('/admin/joueurs', name: 'admin_manage_players')]
    public function managePlayers(JoueursRepository $joueurRepository): Response
    {
        $joueurs = $joueurRepository->findAll();

        return $this->render('pages/admin/manage_players.html.twig', [
            'joueurs' => $joueurs,
        ]);
    }

    #[Route('/admin/roles', name: 'admin_manage_roles')]
    public function manageRoles(PlayerRoleRepository $playerRoleRepository): Response
    {
        $roles = $playerRoleRepository->findAll();

        return $this->render('pages/admin/manage_roles.html.twig', [
            'roles' => $roles,
        ]);
    }

    #[Route('/admin/set-admin/', name: 'admin_settings', methods: ['GET', 'POST'])]
    public function settings()
    {
        // Logique pour gérer les paramètres
        return $this->render('pages/admin/settings.html.twig');
    }
}
