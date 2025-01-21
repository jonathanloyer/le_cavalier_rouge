<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\FeuilleMatch;
use App\Repository\FeuilleMatchRepository;
use App\Repository\ClubRepository;
use App\Repository\UserRepository;
use Dompdf\Dompdf;
use Dompdf\Options;

class MatchSheetsController extends AbstractController
{
    // Route pour afficher la page d'accueil des feuilles de match
    #[Route('/feuille-de-match', name: 'app_match_sheets')]
    public function index(): Response
    {
        return $this->render('pages/match_sheets/matchsheets.html.twig');
    }

    // Route pour créer une feuille de match
    #[Route('/feuille-de-match/creer/{type}', name: 'app_match_sheets_create', defaults: ['type' => null])]
    public function create(?string $type, ClubRepository $clubRepository): Response
    {
        // Vérification des rôles : seuls les administrateurs et capitaines peuvent accéder
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_CAPITAINE')) {
            throw $this->createAccessDeniedException('Accès refusé.');
        }

        // Définir le type par défaut si le type fourni est invalide
        if (!in_array($type, ['criterium', 'national'])) {
            $type = 'criterium'; // Valeur par défaut
        }

        // Récupération des clubs disponibles
        $clubs = $clubRepository->findAll();

        // Rendu de la vue pour créer une feuille de match
        return $this->render('pages/match_sheets/create.html.twig', [
            'type' => $type,
            'clubs' => $clubs,
        ]);
    }

    // Route pour enregistrer une feuille de match
    #[Route('/feuille-de-match/save', name: 'app_match_sheets_save', methods: ['POST'])]
    public function save(Request $request, EntityManagerInterface $em, ClubRepository $clubRepo): Response
    {
        $formData = $request->request->all();

        // Récupération des clubs
        $clubA = $clubRepo->find($formData['clubA']);
        $clubB = $clubRepo->find($formData['clubB']);

        if (!$clubA || !$clubB) {
            throw $this->createNotFoundException('Clubs invalides.');
        }

        // Création de la feuille de match
        $feuilleMatch = new FeuilleMatch();
        $feuilleMatch->setType($formData['typeFeuille'] ?? 'criterium'); // Utilise le choix ou 'criterium' par défaut
        $feuilleMatch->setGroupe($formData['groupe']); // "Groupe 1" ou "Groupe 2"
        $feuilleMatch->setInterclub($formData['interclub']); // "Interclub Jeune"
        $feuilleMatch->setClubA($clubA);
        $feuilleMatch->setClubB($clubB);
        $feuilleMatch->setDateMatch(new \DateTimeImmutable($formData['dateMatch'] ?? 'now'));
        $feuilleMatch->setCreation(new \DateTimeImmutable());

        // Attribuer une valeur par défaut à `ronde`
        $feuilleMatch->setRonde(1); // Remplacez 1 par une autre valeur par défaut si nécessaire

        // Gestion des joueurs et rôles
        $joueurs = [];

        if (!empty($formData['capitaineA'])) {
            $joueurs[] = [
                'id' => $formData['capitaineA'],
                'role' => 'capitaineA',
            ];
        }

        if (!empty($formData['capitaineB'])) {
            $joueurs[] = [
                'id' => $formData['capitaineB'],
                'role' => 'capitaineB',
            ];
        }

        if (!empty($formData['arbitre'])) {
            $joueurs[] = [
                'id' => $formData['arbitre'],
                'role' => 'arbitre',
            ];
        }

        foreach ($formData['joueursA'] ?? [] as $joueurA) {
            $joueurs[] = [
                'id' => $joueurA,
                'role' => 'joueurA',
            ];
        }

        foreach ($formData['joueursB'] ?? [] as $joueurB) {
            $joueurs[] = [
                'id' => $joueurB,
                'role' => 'joueurB',
            ];
        }

        $feuilleMatch->setJoueurs($joueurs ?: []); // Assurez-vous que `joueurs` est un tableau

        // Enregistrement en base
        $em->persist($feuilleMatch);
        $em->flush();

        return $this->redirectToRoute('app_match_sheets_list');
    }


    // Route pour afficher la liste des feuilles de match
    #[Route('/feuille-de-match/consulter', name: 'app_match_sheets_list')]
    public function list(FeuilleMatchRepository $feuilleMatchRepo): Response
    {
        // Récupération de toutes les feuilles de match
        $feuilles = $feuilleMatchRepo->findAll();

        // S'assurer que chaque feuille a un tableau valide pour `joueurs`
        foreach ($feuilles as $feuille) {
            if ($feuille->getJoueurs() === null) {
                $feuille->setJoueurs([]);
            }
        }

        // Rendu de la vue avec la liste des feuilles
        return $this->render('pages/match_sheets/list.html.twig', [
            'feuilles' => $feuilles,
        ]);
    }

    // Route pour afficher une feuille de match
    #[Route('/feuille-de-match/{id}', name: 'app_match_sheets_show')]
    public function show(int $id, FeuilleMatchRepository $feuilleMatchRepo, UserRepository $userRepo): Response
    {
        // Récupération de la feuille de match
        $feuilleMatch = $feuilleMatchRepo->find($id);

        if (!$feuilleMatch) {
            $this->addFlash('error', "Feuille de match introuvable.");
            return $this->redirectToRoute('app_match_sheets_list');
        }

        // Validation des données des joueurs
        $roles = $feuilleMatch->getJoueurs() ?? [];
        $capitaineA = isset($roles['capitaineA']) ? $userRepo->find($roles['capitaineA']) : null;
        $capitaineB = isset($roles['capitaineB']) ? $userRepo->find($roles['capitaineB']) : null;
        $arbitre = isset($roles['arbitre']) ? $userRepo->find($roles['arbitre']) : null;

        // Rendu de la vue avec les données nécessaires
        return $this->render('pages/match_sheets/show.html.twig', [
            'feuilleMatch' => $feuilleMatch,
            'capitaineA' => $capitaineA,
            'capitaineB' => $capitaineB,
            'arbitre' => $arbitre,
        ]);
    }


    #[Route('/feuille-de-match/{id}/modifier', name: 'app_match_sheets_edit', methods: ['GET', 'POST'])]
public function edit(
    int $id,
    Request $request,
    FeuilleMatchRepository $feuilleMatchRepo,
    EntityManagerInterface $em,
    ClubRepository $clubRepository,
    UserRepository $userRepo
): Response {
    // Récupération de la feuille de match
    $feuilleMatch = $feuilleMatchRepo->find($id);

    if (!$feuilleMatch) {
        $this->addFlash('error', 'Feuille de match introuvable.');
        return $this->redirectToRoute('app_match_sheets_list');
    }

    // Initialisation des tableaux pour les joueurs sélectionnés et les résultats
    $joueursSelectionnesA = array_fill(0, 5, null);
    $joueursSelectionnesB = array_fill(0, 5, null);
    $resultats = array_fill(0, 5, '');

    // Parcourir les joueurs dans la feuille de match pour remplir les tableaux
    foreach ($feuilleMatch->getJoueurs() as $joueur) {
        if ($joueur['role'] === 'joueurA') {
            $joueursSelectionnesA[] = $userRepo->find($joueur['id']);
        } elseif ($joueur['role'] === 'joueurB') {
            $joueursSelectionnesB[] = $userRepo->find($joueur['id']);
        }
        if (isset($joueur['resultat'])) {
            $resultats[] = $joueur['resultat'];
        }
    }

    // Préparation des capitaines et de l'arbitre
    $capitainesEtArbitre = [
        'capitaineA' => null,
        'capitaineB' => null,
        'arbitre' => null,
    ];
    foreach ($feuilleMatch->getJoueurs() as $joueur) {
        if ($joueur['role'] === 'capitaineA') {
            $capitainesEtArbitre['capitaineA'] = $userRepo->find($joueur['id']);
        } elseif ($joueur['role'] === 'capitaineB') {
            $capitainesEtArbitre['capitaineB'] = $userRepo->find($joueur['id']);
        } elseif ($joueur['role'] === 'arbitre') {
            $capitainesEtArbitre['arbitre'] = $userRepo->find($joueur['id']);
        }
    }

    // Récupération des clubs et des joueurs associés
    $clubs = $clubRepository->findAll();
    $joueursA = $feuilleMatch->getClubA() ? $feuilleMatch->getClubA()->getUsers() : [];
    $joueursB = $feuilleMatch->getClubB() ? $feuilleMatch->getClubB()->getUsers() : [];

    // Transmission des données à la vue
    return $this->render('pages/match_sheets/edit.html.twig', [
        'feuilleMatch' => $feuilleMatch,
        'clubs' => $clubs,
        'joueursA' => $joueursA,
        'joueursB' => $joueursB,
        'joueursSelectionnesA' => $joueursSelectionnesA,
        'joueursSelectionnesB' => $joueursSelectionnesB,
        'resultats' => $resultats,
        'capitainesEtArbitre' => $capitainesEtArbitre,
        'interclub' => $feuilleMatch->getInterclub(), // Ajout d'interclub
        'division' => $feuilleMatch->getType(), // Ajout de division
        'groupe' => $feuilleMatch->getGroupe(), // Ajout de groupe
    ]);
}

    

    // Route pour supprimer une feuille de match
    #[Route('/feuille-de-match/{id}/supprimer', name: 'app_match_sheets_delete', methods: ['POST'])]
    public function delete($id, FeuilleMatchRepository $feuilleMatchRepo, EntityManagerInterface $em): Response
    {
        // Récupération de la feuille de match
        $feuilleMatch = $feuilleMatchRepo->find($id);

        // Suppression si elle existe
        if ($feuilleMatch) {
            $em->remove($feuilleMatch);
            $em->flush();
        }

        // Redirection vers la liste
        return $this->redirectToRoute('app_match_sheets_list');
    }

    // Route pour récupérer les joueurs d'un club
    #[Route('/feuille-de-match/club/{id}/joueurs', name: 'app_match_sheets_get_players_by_club', methods: ['GET'])]
    public function getPlayersByClub(int $id, ClubRepository $clubRepository): Response
    {
        // Récupération du club
        $club = $clubRepository->find($id);

        if (!$club) {
            return $this->json(['error' => 'Club introuvable'], Response::HTTP_NOT_FOUND);
        }

        // Filtrage des joueurs actifs
        $joueurs = $club->getUsers()->filter(fn($user) => $user->getActive());

        // Conversion en format JSON
        $data = array_map(fn($joueur) => [
            'id' => $joueur->getId(),
            'codeFfe' => $joueur->getCodeFFE(),
            'firstName' => $joueur->getFirstName(),
            'lastName' => $joueur->getLastName(),
        ], $joueurs->toArray());

        return $this->json($data);
    }

    // Route pour télécharger une feuille de match au format PDF
    #[Route('/feuille-de-match/{id}/pdf', name: 'app_match_sheets_download_pdf')]
public function downloadPdf(
    int $id,
    FeuilleMatchRepository $feuilleMatchRepo,
    UserRepository $userRepo
): Response {
    // Récupération de la feuille de match
    $feuilleMatch = $feuilleMatchRepo->find($id);

    if (!$feuilleMatch) {
        throw $this->createNotFoundException("Feuille de match introuvable.");
    }

    // Récupération des capitaines et de l'arbitre
    $roles = $feuilleMatch->getJoueurs() ?? [];
    $capitaineA = null;
    $capitaineB = null;
    $arbitre = null;

    foreach ($roles as $role) {
        if ($role['role'] === 'capitaineA') {
            $capitaineA = $userRepo->find($role['id']);
        } elseif ($role['role'] === 'capitaineB') {
            $capitaineB = $userRepo->find($role['id']);
        } elseif ($role['role'] === 'arbitre') {
            $arbitre = $userRepo->find($role['id']);
        }
    }

    // Génération du contenu HTML pour le PDF
    $html = $this->renderView('pages/match_sheets/pdf.html.twig', [
        'feuilleMatch' => $feuilleMatch,
        'capitaineA' => $capitaineA,
        'capitaineB' => $capitaineB,
        'arbitre' => $arbitre,
    ]);

    // Configuration de Dompdf
    $options = new Options();
    $options->set('defaultFont', 'Arial');
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Retourne le fichier PDF en téléchargement
    return new Response($dompdf->output(), 200, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'attachment; filename="feuille_de_match_' . $feuilleMatch->getId() . '.pdf"',
    ]);
}

}
