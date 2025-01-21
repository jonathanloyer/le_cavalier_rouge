<?php

namespace App\Controller;

use App\Document\Contact;
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
use App\Service\MongoDBClient;
use MongoDB\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use MongoDB\BSON\ObjectId;

class AdminController extends AbstractController
{

    // Page d'accueil de l'administrateur
    #[Route('/admin', name: 'app_admin')]
    public function index(
        UserRepository $userRepository,  // Injection de dépendance pour UserRepository
        ClubRepository $clubRepository, // Injection de dépendance pour ClubRepository
        CompetitionsRepository $competitionRepository, // Injection de dépendance pour CompetitionsRepository
        FeuilleMatchRepository $feuilleMatchRepository, // Injection de dépendance pour FeuilleMatchRepository

    ): Response {

        // Vérifier si l'utilisateur est connecté
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {

            // Rediriger vers la page de connexion
            return $this->redirectToRoute('app_login');
        }

        // Vérifier si l'utilisateur est un administrateur
        if (!$this->isGranted('ROLE_ADMIN')) {

            // Rediriger vers la page de profil
            return $this->redirectToRoute('app_profile');
        }

        // Récupérer le nombre total d'utilisateurs
        $totalUsers = $userRepository->count([]);

        // Récupérer le nombre total de joueurs actifs
        $totalJoueurs = $userRepository->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.active = :active')
            ->andWhere('u.createdAt >= :startOfMonth') // Si vous utilisez createdAt
            ->setParameter('active', true)
            ->setParameter('startOfMonth', new \DateTime('first day of this month'))
            ->getQuery()
            ->getSingleScalarResult();

        // Récupérer le nombre total de clubs
        $totalClubs = $clubRepository->count([]);

        // Récupérer le nombre total de compétitions
        $totalCompetitions = $competitionRepository->count([]);


        // Récupérer le nombre de matches ce mois-ci
        $matchesThisMonth = $feuilleMatchRepository->createQueryBuilder('f')
            ->select('count(f.id)')
            ->where('f.dateMatch >= :startOfMonth')
            ->setParameter('startOfMonth', new \DateTime('first day of this month'))
            ->getQuery()
            ->getSingleScalarResult();

        // Activités récentes
        $recentActivities = [
            ['message' => 'Nouvel utilisateur inscrit', 'time' => 'il y a 2 heures'],
            ['message' => 'Compétition ajoutée : Open de France', 'time' => 'il y a 1 jour'],
            ['message' => 'Feuille de match soumise', 'time' => 'il y a 3 jours'],
        ];

        // Retourner la vue de la page d'accueil de l'administrateur
        return $this->render('pages/admin/index.html.twig', [
            'totalUsers' => $totalUsers,
            'totalJoueurs' => $totalJoueurs,
            'totalClubs' => $totalClubs,
            'totalCompetitions' => $totalCompetitions,
            'matchesThisMonth' => $matchesThisMonth,
            'recentActivities' => $recentActivities,
        ]);
    }

    // Gérer les utilisateurs
    #[Route('/admin/utilisateurs', name: 'admin_manage_users')]
    public function manageUsers(UserRepository $userRepository): Response
    {
        // Récupérer tous les utilisateurs
        $users = $userRepository->findAll();

        // Retourner la vue pour gérer les utilisateurs
        return $this->render('pages/admin/manage_users.html.twig', [
            'users' => $users,
        ]);
    }

    // Modifier un utilisateur
    #[Route('/admin/utilisateur/{id}/modifier', name: 'admin_edit_user')]
    public function editUser(
        int $id, // Récupére l'ID de l'utilisateur à modifier
        Request $request, // Récupére la requête HTTP
        UserRepository $userRepository, // Injection de dépendance pour UserRepository
        EntityManagerInterface $entityManager // Injection de dépendance pour EntityManagerInterface

    ): Response {

        // Récupére l'utilisateur à modifier
        $user = $userRepository->find($id);

        // Vérifie si l'utilisateur existe
        if (!$user) {

            // Retourne une erreur 404
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        // Crée un formulaire pour modifier l'utilisateur
        $form = $this->createForm(UserType::class, $user);

        // Gére la soumission du formulaire
        $form->handleRequest($request);

        // Vérifie si le formulaire a été soumis et est valide
        if ($form->isSubmitted() && $form->isValid()) {

            // Enregistre les modifications dans la base de données
            $entityManager->flush();

            // J'ajoute un message flash pour confirmer la modification
            $this->addFlash('success', 'Utilisateur modifié avec succès.');

            // je redirige vers la page de gestion des utilisateurs
            return $this->redirectToRoute('admin_manage_users');
        }

        // Retourne la vue du formulaire de modification de l'utilisateur
        return $this->render('pages/admin/edit_user.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Supprimer un utilisateur
    #[Route('/admin/utilisateur/{id}/supprimer', name: 'admin_delete_user', methods: ['POST'])]
    public function deleteUser(int $id, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        // Je récupère l'utilisateur à supprimer par son ID
        $user = $userRepository->find($id);

        // Je vérifie si l'utilisateur existe
        if (!$user) {

            // Je retourne une erreur 404 s'il n'existe pas
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        // Je supprime l'utilisateur de la base de données
        $entityManager->remove($user);

        // J'envoie les modifications à la base de données
        $entityManager->flush();

        // J'ajoute un message flash pour confirmer la suppression
        $this->addFlash('success', 'Utilisateur supprimé avec succès.');

        // Je redirige vers la page de gestion des utilisateurs
        return $this->redirectToRoute('admin_manage_users');
    }

    // Ajouter le rôle capitaine
    #[Route('/admin/utilisateur/{id}/ajouter-capitaine', name: 'admin_add_capitaine')]
    public function addCapitaine(int $id, UserRepository $userRepository, ManagerRegistry $doctrine): Response
    {
        // Je récupère l'utilisateur par son ID
        $user = $userRepository->find($id);

        // Je vérifie si l'utilisateur existe
        if (!$user) {

            // Je retourne une erreur 404 s'il n'existe pas
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }

        // Je récupère les rôles de l'utilisateur
        $roles = $user->getRoles();

        // J'ajoute le rôle capitaine s'il n'existe pas
        if (!in_array('ROLE_CAPITAINE', $roles)) {

            $roles[] = 'ROLE_CAPITAINE';
        }

        // J'attribue les nouveaux rôles à l'utilisateur
        $user->setRoles($roles);

        // J'envoie les modifications à la base de données
        $em = $doctrine->getManager();

        // J'ajoute un message flash pour confirmer l'attribution du rôle
        $em->persist($user);

        // J'envoie les modifications à la base de données
        $em->flush();

        // J'ajoute un message flash pour confirmer l'attribution du rôle
        $this->addFlash('success', 'Le rôle capitaine a été attribué.');

        // Je redirige vers la page de gestion des utilisateurs
        return $this->redirectToRoute('admin_manage_users');
    }

    // Retirer le rôle capitaine
    #[Route('/admin/utilisateur/{id}/retirer-capitaine', name: 'admin_remove_capitaine')]
    public function removeCapitaine(int $id, UserRepository $userRepository, ManagerRegistry $doctrine): Response
    {
        // Je récupère l'utilisateur par son ID
        $user = $userRepository->find($id);

        // Je vérifie si l'utilisateur existe
        if (!$user) {

            // Je retourne une erreur 404 s'il n'existe pas
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }

        // Je retire le rôle capitaine de l'utilisateur
        $roles = array_diff($user->getRoles(), ['ROLE_CAPITAINE']);

        // J'attribue les nouveaux rôles à l'utilisateur
        $user->setRoles($roles);

        // J'envoie les modifications à la base de données
        $em = $doctrine->getManager();

        // J'ajoute un message flash pour confirmer la suppression du rôle
        $em->persist($user);

        // J'envoie les modifications à la base de données
        $em->flush();

        // J'ajoute un message flash pour confirmer la suppression du rôle
        $this->addFlash('success', 'Le rôle capitaine a été retiré.');

        // Je redirige vers la page de gestion des utilisateurs
        return $this->redirectToRoute('admin_manage_users');
    }

    // Gérer les compétitions
    #[Route('/admin/competitions', name: 'admin_manage_competitions')]
    public function manageCompetitions(CompetitionsRepository $competitionRepository): Response
    {
        // Je récupère toutes les compétitions
        $competitions = $competitionRepository->findAll();

        // Je retourne la vue pour gérer les compétitions
        return $this->render('pages/admin/manage_competitions.html.twig', [
            'competitions' => $competitions,
        ]);
    }

    #[Route('/admin/joueurs', name: 'admin_manage_players')]
    public function managePlayers(JoueursRepository $joueursRepository): Response
    {
        // Je récupère tous les joueurs
        $joueurs = $joueursRepository->findAll();

        // Je retourne la vue pour gérer les joueurs
        return $this->render('pages/admin/manage_players.html.twig', [
            'joueurs' => $joueurs,
        ]);
    }

    #[Route('/admin/feuilles-de-match', name: 'admin_manage_match_sheets')]
public function manageMatchSheets(FeuilleMatchRepository $feuilleMatchRepository): Response
{
    // Récupérer toutes les feuilles de match
    $feuillesMatch = $feuilleMatchRepository->findAll();

    // Vérifier et corriger les feuilles avec des données invalides pour `joueurs`
    foreach ($feuillesMatch as $feuille) {
        if (!is_array($feuille->getJoueurs())) {
            $feuille->setJoueurs([]); // Définit un tableau vide par défaut si nécessaire
        }
    }

    // Retourner la vue pour gérer les feuilles de match
    return $this->render('pages/admin/manage_match_sheets.html.twig', [
        'feuillesMatch' => $feuillesMatch,
    ]);
}


    // Gérer les clubs
    #[Route('/admin/clubs', name: 'admin_manage_clubs')]
    public function manageClubs(
        ClubRepository $clubRepository, // Injection de dépendance pour ClubRepository
        EntityManagerInterface $entityManager, // Injection de dépendance pour EntityManagerInterface
        Request $request // Injection de dépendance pour Request

    ): Response {

        // Je récupère tous les clubs
        $clubs = $clubRepository->findAll();

        // Si le formulaire est soumis
        if ($request->isMethod('POST')) {

            // Je récupère le nom du club
            $clubName = $request->request->get('name');

            // Si le nom du club n'est pas vide
            if ($clubName) {

                // Je crée une nouvelle instance de Club
                $club = new Club();

                // J'attribue le nom du club
                $club->setName($clubName);

                // J'envoie les modifications à la base de données
                $entityManager->persist($club);

                // J'ajoute un message flash pour confirmer la création du club
                $entityManager->flush();

                // J'ajoute un message flash pour confirmer la création du club
                $this->addFlash('success', 'Le club a été créé avec succès.');

                // Je redirige vers la page de gestion des clubs
                return $this->redirectToRoute('admin_manage_clubs');
            }

            // J'ajoute un message flash pour indiquer que le nom du club ne peut pas être vide
            $this->addFlash('error', 'Le nom du club ne peut pas être vide.');
        }

        // Je retourne la vue pour gérer les clubs
        return $this->render('pages/admin/manage_clubs.html.twig', [
            'clubs' => $clubs,
        ]);
    }

    // Modifier un club
    #[Route('/admin/clubs/{id}/edit', name: 'admin_edit_club')]
    public function editClub(

        int $id, // Récupère l'ID du club à modifier
        Request $request, // Je récupère la requête HTTP
        ClubRepository $clubRepository, // Injection de dépendance pour ClubRepository
        EntityManagerInterface $entityManager // Injection de dépendance pour EntityManagerInterface

    ): Response {

        // Je récupère le club à modifier
        $club = $clubRepository->find($id);

        // Je vérifie si le club existe
        if (!$club) {

            // Je retourne une erreur 404 s'il n'existe pas
            throw $this->createNotFoundException('Club introuvable.');
        }

        // Si le formulaire est soumis
        if ($request->isMethod('POST')) {

            // Je récupère le nom du club
            $clubName = $request->request->get('name');

            // Si le nom du club n'est pas vide
            if ($clubName) {

                // J'attribue le nouveau nom du club
                $club->setName($clubName);

                // J'envoie les modifications à la base de données
                $entityManager->flush();

                // J'ajoute un message flash pour confirmer la modification
                $this->addFlash('success', 'Le club a été mis à jour avec succès.');

                // Je redirige vers la page de gestion des clubs
                return $this->redirectToRoute('admin_manage_clubs');
            }

            // J'ajoute un message flash pour indiquer que le nom du club ne peut pas être vide
            $this->addFlash('error', 'Le nom du club ne peut pas être vide.');
        }

        // Je retourne la vue pour modifier le club
        return $this->render('pages/admin/edit_club.html.twig', [
            'club' => $club,
        ]);
    }

    // Supprimer un club
    #[Route('/admin/clubs/{id}/delete', name: 'admin_delete_club', methods: ['POST'])]
    public function deleteClub(int $id, ClubRepository $clubRepository, EntityManagerInterface $entityManager): Response
    {
        // Je récupère le club à supprimer par son ID
        $club = $clubRepository->find($id);

        // Je vérifie si le club existe
        if ($club) {

            // Je supprime le club de la base de données
            $entityManager->remove($club);

            // J'envoie les modifications à la base de données
            $entityManager->flush();

            // J'ajoute un message flash pour confirmer la suppression
            $this->addFlash('success', 'Le club a été supprimé avec succès.');
        } else {
            // J'ajoute un message flash pour indiquer que le club n'existe pas
            $this->addFlash('error', 'Le club n\'existe pas.');
        }

        // Je redirige vers la page de gestion des clubs
        return $this->redirectToRoute('admin_manage_clubs');
    }

    #[Route('/admin/parametres', name: 'admin_settings')]
    public function settings(Request $request): Response
    {
        // Vous pouvez ajouter une logique spécifique pour gérer les paramètres ici
        // Par exemple, charger des configurations ou traiter un formulaire

        return $this->render('pages/admin/settings.html.twig');
    }

    // Gérer les joueurs actifs
    #[Route('/admin/joueurs/actifs', name: 'admin_active_players')]
    public function manageActivePlayers(UserRepository $userRepository): Response
    {
        // Récupère uniquement les utilisateurs actifs
        $activePlayers = $userRepository->findBy(['active' => true]);

        // Vérifie si aucun utilisateur actif n'a été trouvé
        if (empty($activePlayers)) {
            throw new \Exception('Aucun utilisateur actif trouvé. Vérifiez les données dans la base de données.');
        }

        // Retourne la vue pour gérer les joueurs actifs
        return $this->render('pages/admin/manage_active_players.html.twig', [
            'joueurs' => $activePlayers,
        ]);
    }

    #[Route('/admin/contacts', name: 'admin_manage_contacts')]
    public function manageContacts(Client $mongoClient): Response
{
    $db = $mongoClient->selectDatabase($_ENV['MONGODB_DB']);
    $contacts = $db->contacts->find()->toArray();

    return $this->render('pages/admin/manage_contacts.html.twig', [
        'contacts' => $contacts,
    ]);
}




    #[Route('/admin/contacts/{id}/delete', name: 'admin_delete_contact', methods: ['POST'])]
    public function deleteContact(string $id, Client $mongoClient): Response
    {
        try {
            // Sélectionner la base de données et la collection
            $db = $mongoClient->selectDatabase($_ENV['MONGODB_DB']);
            $collection = $db->contacts;

            // Recherche du document avant suppression
            $contact = $collection->findOne(['_id' => $id]); // Utilisation de l'ID en tant que chaîne

            if (!$contact) {
                $this->addFlash('error', 'Message introuvable.');
                return $this->redirectToRoute('admin_manage_contacts');
            }

            // Suppression du document
            $result = $collection->deleteOne(['_id' => $id]);

            if ($result->getDeletedCount() > 0) {
                $this->addFlash('success', 'Message supprimé avec succès.');
            } else {
                $this->addFlash('error', 'Impossible de supprimer le message.');
            }
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }

        return $this->redirectToRoute('admin_manage_contacts');
    }
}
