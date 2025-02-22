<?php

namespace App\Controller;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\FeuilleMatch;
use App\Repository\ClubRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\FeuilleMatchRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
    public function save(Request $request, EntityManagerInterface $em, ClubRepository $clubRepo, UserRepository $userRepo): Response
    {

        $formData = $request->request->all(); // Récupération des données du formulaire
 // ========== AJOUT : vérification des champs obligatoires ==========
        // Liste des champs obligatoires
        $requiredFields = [
            'clubA',
            'clubB',
            'capitaineA',
            'capitaineB',
            'arbitre',
            'joueursA',   // On veut s'assurer qu'il y a bien un tableau de joueursA
            'joueursB',   // Idem pour joueursB
            'resultats',  // Et un tableau de resultats
        ];

        $missingFields = [];

        // Vérifier si chaque champ est bien présent et non vide
        foreach ($requiredFields as $field) {
            if (!isset($formData[$field]) || empty($formData[$field])) {
                $missingFields[] = $field;
            }
        }

        // S'il manque au moins un champ, on redirige en affichant un message d'erreur
        if (!empty($missingFields)) {
            foreach ($missingFields as $field) {
                $this->addFlash('error', "Le champ '$field' est obligatoire ou vide.");
            }
            // Redirection vers la page de création (vous pouvez ajuster 'type')
            return $this->redirectToRoute('app_match_sheets_create', [
                'type' => 'criterium'
            ]);
        }
        // ========== FIN AJOUT ==========

        // Récupération des clubs par leur id
        $clubA = $clubRepo->find($formData['clubA']);
        $clubB = $clubRepo->find($formData['clubB']);

        if (!$clubA || !$clubB) { // Vérification de l'existence des clubs
            throw $this->createNotFoundException('Clubs invalides.'); // Erreur 404
        }

        // Récupération des capitaines et de l'arbitre
        $capitaineA = $userRepo->find($formData['capitaineA']); // Récupération du capitaine A
        $capitaineB = $userRepo->find($formData['capitaineB']); // Récupération du capitaine B
        $arbitre = $userRepo->find($formData['arbitre']); // Récupération de l'arbitre

        if (!$capitaineA || !$capitaineB || !$arbitre) {   // Vérification de l'existence des capitaines et de l'arbitre
            throw $this->createNotFoundException('Capitaines ou arbitre invalides.'); // sinon je retourn une erreur 404
        }

        // Création de la feuille de match
        $feuilleMatch = new FeuilleMatch();
        $feuilleMatch->setType($formData['division'] ?? 'criterium'); // Utilise le choix ou 'criterium' par défaut
        $feuilleMatch->setGroupe($formData['groupe']); // "Groupe 1" ou "Groupe 2"
        $feuilleMatch->setInterclub($formData['interclub']); // "Interclub Jeune"
        $feuilleMatch->setClubA($clubA); // Club A
        $feuilleMatch->setClubB($clubB); // Club B
        $feuilleMatch->setDateMatch(new \DateTimeImmutable($formData['dateMatch'] ?? 'now')); // Date du match
        $feuilleMatch->setCreation(new \DateTimeImmutable()); // Date de création

        // Attribuer une valeur par défaut à `ronde`
        $feuilleMatch->setRonde(1);

        // Gestion des joueurs et résultats
        $joueurs = [];

        // Boucle sur les joueurs A pour les ajouter à la feuille de match
        foreach ($formData['joueursA'] as $index => $joueurA) {
            $joueurs[] = [
                'joueurA' => $joueurA,
                'resultat' => $formData['resultats'][$index] ?? null,
                'joueurB' => $formData['joueursB'][$index] ?? null,
            ];
        }

        // Ajout des rôles spécifiques
        $joueurs[] = [
            'id' => $formData['capitaineA'],
            'role' => 'capitaineA',
        ];
        $joueurs[] = [
            'id' => $formData['capitaineB'],
            'role' => 'capitaineB',
        ];
        $joueurs[] = [
            'id' => $formData['arbitre'],
            'role' => 'arbitre',
        ];

        // Ajout des joueurs à la feuille de match
        $feuilleMatch->setJoueurs($joueurs);

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
    public function show(
        int $id,
        FeuilleMatchRepository $feuilleMatchRepo,
        UserRepository $userRepo
    ): Response {
        // je récupère la feuille de match
        $feuilleMatch = $feuilleMatchRepo->find($id);

        //Je vérifie si la feuille de match existe
        if (!$feuilleMatch) {
            $this->addFlash('error', "Feuille de match introuvable.");
            return $this->redirectToRoute('app_match_sheets_list');
        }

        //Je récupère les clubs
        $clubA = $feuilleMatch->getClubA();
        $clubB = $feuilleMatch->getClubB();

        // Préparation des joueurs et résultats
        $joueursA = [];
        $joueursB = [];
        $resultats = [];

        // je récupère les joueurs et les résultats
        foreach ($feuilleMatch->getJoueurs() as $joueur) {
            if (isset($joueur['joueurA'])) {
                $joueursA[] = $userRepo->find($joueur['joueurA']);
            }
            if (isset($joueur['joueurB'])) {
                $joueursB[] = $userRepo->find($joueur['joueurB']);
            }
            if (isset($joueur['resultat'])) {
                $resultats[] = $joueur['resultat'];
            }
        }

        // Récupération des capitaines et de l'arbitre
        $capitainesEtArbitre = [
            'capitaineA' => null,
            'capitaineB' => null,
            'arbitre' => null,
        ];

        // je récupère les capitaines et l'arbitre grace à leur role
        foreach ($feuilleMatch->getJoueurs() as $joueur) {
            if (isset($joueur['role']) && $joueur['role'] === 'capitaineA') {
                $capitainesEtArbitre['capitaineA'] = $userRepo->find($joueur['id']);
            } elseif (isset($joueur['role']) && $joueur['role'] === 'capitaineB') {
                $capitainesEtArbitre['capitaineB'] = $userRepo->find($joueur['id']);
            } elseif (isset($joueur['role']) && $joueur['role'] === 'arbitre') {
                $capitainesEtArbitre['arbitre'] = $userRepo->find($joueur['id']);
            }
        }

        // Rendu de la vue pour afficher la feuille de match
        return $this->render('pages/match_sheets/show.html.twig', [
            'feuilleMatch' => $feuilleMatch,
            'clubA' => $clubA,
            'clubB' => $clubB,
            'joueursA' => $joueursA,
            'joueursB' => $joueursB,
            'resultats' => $resultats,
            'capitainesEtArbitre' => $capitainesEtArbitre,
        ]);
    }


    // Route pour modifier une feuille de match
    #[Route('/feuille-de-match/{id}/modifier', name: 'app_match_sheets_edit', methods: ['GET', 'POST'])]
    public function edit(

        int $id, // Identifiant de la feuille de match
        Request $request, // Requête HTTP
        FeuilleMatchRepository $feuilleMatchRepo, // Repository des feuilles de match
        EntityManagerInterface $em, // EntityManager pour la base de données
        ClubRepository $clubRepository, // Repository des clubs
        UserRepository $userRepo // Repository des utilisateurs

    ): Response {

        // Vérification des rôles : seuls les administrateurs et capitaines peuvent accéder
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_CAPITAINE')) {
            throw $this->createAccessDeniedException('Accès refusé.');
        }

        // Je récupère la feuille de match
        $feuilleMatch = $feuilleMatchRepo->find($id);

        // Je vérifie si la feuille de match existe
        if (!$feuilleMatch) {
            $this->addFlash('error', 'Feuille de match introuvable.');
            return $this->redirectToRoute('app_match_sheets_list');
        }

        // Je vérifie si l'utilisateur a les droits pour modifier la feuille de match
        if ($request->isMethod('POST')) {
            $formData = $request->request->all(); // Récupération des données du formulaire

            // Mise à jour des champs de la feuille de match
            $clubA = $clubRepository->find($formData['clubA']);
            $clubB = $clubRepository->find($formData['clubB']);

            // Je vérifie si les clubs existent
            if ($clubA && $clubB) {
                $feuilleMatch->setClubA($clubA);
                $feuilleMatch->setClubB($clubB);
            }

            // Mise à jour des autres champs
            $feuilleMatch->setType($formData['division'] ?? $feuilleMatch->getType()); // Utilise le choix ou 'criterium' par défaut

            $feuilleMatch->setGroupe($formData['groupe'] ?? $feuilleMatch->getGroupe()); // "Groupe 1" ou "Groupe 2"

            $feuilleMatch->setInterclub($formData['interclub'] ?? $feuilleMatch->getInterclub()); // "Interclub Jeune"

            $feuilleMatch->setDateMatch(new \DateTimeImmutable($formData['dateMatch'] ?? $feuilleMatch->getDateMatch()->format('Y-m-d'))); // Date du match

            // Mise à jour des joueurs
            $joueurs = [];

            // Je boucle sur les joueurs A pour les ajouter à la feuille de match
            foreach ($formData['joueursA'] as $index => $joueurA) {

                $joueurs[] = [

                    'joueurA' => $joueurA, // Récupération des joueurs A
                    'resultat' => $formData['resultats'][$index] ?? null, // Récupération des résultats
                    'joueurB' => $formData['joueursB'][$index] ?? null, // Récupération des joueurs B
                ];
            }

            // Mise à jour des rôles spécifiques
            $joueurs[] = [
                'id' => $formData['capitaineA'],
                'role' => 'capitaineA',
            ];
            $joueurs[] = [
                'id' => $formData['capitaineB'],
                'role' => 'capitaineB',
            ];
            $joueurs[] = [
                'id' => $formData['arbitre'],
                'role' => 'arbitre',
            ];

            // Je mets à jour les joueurs
            $feuilleMatch->setJoueurs($joueurs);

            // Sauvegarde des modifications
            $em->persist($feuilleMatch);
            $em->flush();

            // Je redirige l'utilisateur vers la liste des feuilles de match
            $this->addFlash('success', 'La feuille de match a été mise à jour avec succès.');
            return $this->redirectToRoute('app_match_sheets_list');
        }

        // Préparation des données pour le formulaire d'édition
        $joueursSelectionnesA = []; 
        $joueursSelectionnesB = [];
        $resultats = [];

        // je récupère les joueurs et les résultats
        foreach ($feuilleMatch->getJoueurs() as $joueur) {
            if (isset($joueur['joueurA'])) {
                $joueursSelectionnesA[] = $userRepo->find($joueur['joueurA']);
            }
            if (isset($joueur['joueurB'])) {
                $joueursSelectionnesB[] = $userRepo->find($joueur['joueurB']);
            }
            if (isset($joueur['resultat'])) {
                $resultats[] = $joueur['resultat'];
            }
        }

        // Récupération des capitaines et de l'arbitre
        $capitainesEtArbitre = [
            'capitaineA' => null,
            'capitaineB' => null,
            'arbitre' => null,
        ];

        // je récupère les capitaines et l'arbitre grace à leur role
        foreach ($feuilleMatch->getJoueurs() as $joueur) {

            if (isset($joueur['role']) && $joueur['role'] === 'capitaineA') {

                $capitainesEtArbitre['capitaineA'] = $userRepo->find($joueur['id']);

            } elseif (isset($joueur['role']) && $joueur['role'] === 'capitaineB') {

                $capitainesEtArbitre['capitaineB'] = $userRepo->find($joueur['id']);

            } elseif (isset($joueur['role']) && $joueur['role'] === 'arbitre') {

                $capitainesEtArbitre['arbitre'] = $userRepo->find($joueur['id']);
            }
        }

        // Récupération des clubs
        $clubs = $clubRepository->findAll();

        $joueursA = $feuilleMatch->getClubA() ? $feuilleMatch->getClubA()->getUsers() : [];

        $joueursB = $feuilleMatch->getClubB() ? $feuilleMatch->getClubB()->getUsers() : [];


        // Rendu de la vue pour modifier la feuille de match
        return $this->render('pages/match_sheets/edit.html.twig', [
            'feuilleMatch' => $feuilleMatch,
            'clubs' => $clubs,
            'joueursA' => $joueursA,
            'joueursB' => $joueursB,
            'joueursSelectionnesA' => $joueursSelectionnesA,
            'joueursSelectionnesB' => $joueursSelectionnesB,
            'resultats' => $resultats,
            'capitainesEtArbitre' => $capitainesEtArbitre,
            'interclub' => $feuilleMatch->getInterclub(),
            'division' => $feuilleMatch->getType(),
            'groupe' => $feuilleMatch->getGroupe(),
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
            $em->remove($feuilleMatch); // Suppression de la feuille de match
            $em->flush(); // Sauvegarde en base de données
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
        int $id, // Identifiant de la feuille de match
        FeuilleMatchRepository $feuilleMatchRepo, // Repository des feuilles de match
        UserRepository $userRepo // Repository des utilisateurs
    ): Response {

        // 1. je récupère la feuille de match par son id
        $feuilleMatch = $feuilleMatchRepo->find($id);

        if (!$feuilleMatch) { // je vérifie si la feuille de match existe
            throw $this->createNotFoundException('Feuille de match introuvable');
        }

        // 2. Récupération des clubs
        $clubA = $feuilleMatch->getClubA(); 
        $clubB = $feuilleMatch->getClubB();

        // 3. Préparation des joueurs et résultats (comme dans show)
        $joueursA = []; 
        $joueursB = [];
        $resultats = [];

        foreach ($feuilleMatch->getJoueurs() as $joueur) { // je récupère les joueurs et les résultats
            if (isset($joueur['joueurA'])) {// si le joueur A existe
                $joueursA[] = $userRepo->find($joueur['joueurA']);
            }
            if (isset($joueur['joueurB'])) {
                $joueursB[] = $userRepo->find($joueur['joueurB']);
            }
            if (isset($joueur['resultat'])) { // je vérifie si le résultat existe
                $resultats[] = $joueur['resultat'];// il se retourne dans le tableau des résultats
            }
        }

        // 4. Récupération des capitaines et de l'arbitre
        $capitainesEtArbitre = [
            'capitaineA' => null, // je définis les capitaines et l'arbitre à null car ils peuvent ne pas être définis
            'capitaineB' => null,
            'arbitre'    => null,
        ];

        foreach ($feuilleMatch->getJoueurs() as $joueur) { // je récupère les capitaines et l'arbitre grace à leur role
            if (isset($joueur['role']) && $joueur['role'] === 'capitaineA') {// je récupère le capitaine A
                $capitainesEtArbitre['capitaineA'] = $userRepo->find($joueur['id']); // si le role est capitaineA
            } elseif (isset($joueur['role']) && $joueur['role'] === 'capitaineB') { 
                $capitainesEtArbitre['capitaineB'] = $userRepo->find($joueur['id']); 
            } elseif (isset($joueur['role']) && $joueur['role'] === 'arbitre') { //si le role est arbitre
                $capitainesEtArbitre['arbitre'] = $userRepo->find($joueur['id']); // je récupère l'arbitre
            }
        }

        // 5. Génération du contenu HTML pour le PDF
        $html = $this->renderView('pages/match_sheets/pdf.html.twig', [
            'feuilleMatch'        => $feuilleMatch, // je passe les données à la vue
            'clubA'               => $clubA, // je passe les clubs
            'clubB'               => $clubB, 
            'joueursA'            => $joueursA, // je passe les joueurs et les résultats
            'joueursB'            => $joueursB,
            'resultats'           => $resultats,
            'capitainesEtArbitre' => $capitainesEtArbitre, // je passe les capitaines et l'arbitre
        ]);

        // 6. Configuration de Dompdf
        $options = new Options(); // Création d'une instance de Options
        $options->set('defaultFont', 'Arial'); // Police par défaut

        // Pour autoriser le chargement de ressources externes (CDN) :
        // (ex: tailwind via CDN)
        $options->set('isRemoteEnabled', true); 

        $dompdf = new Dompdf($options); // j'ai créé une instance de Dompdf afin de générer le PDF
        $dompdf->loadHtml($html); // je charge le contenu HTML car Dompdf ne peut pas charger directement une URL
        $dompdf->setPaper('A4', 'portrait'); // Format et orientation
        $dompdf->render(); // Génération du PDF

        // 7. Retourne le fichier PDF en téléchargement
        return new Response(
            $dompdf->output(), // Contenu du PDF
            200, // Code de statut HTTP qui sert à indiquer que la requête a abouti
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="feuille_de_match_' . $feuilleMatch->getId() . '.pdf"',
                // j'ai fais en sorte que le nom du fichier PDF téléchargé contienne l'identifiant de la feuille de match ainsi lorsque l'admin ou le capitaine télécharge le fichier, il saura à quelle feuille de match il correspond
            ]
        );
    }
}
