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
    public function save(Request $request, EntityManagerInterface $em, ClubRepository $clubRepo, UserRepository $userRepo): Response
    {
        $formData = $request->request->all();

        // Ajoutez dd ici pour inspecter les données du formulaire
        // dd($formData); je test afin de savoir ce qui est envoyé dans mon formulaire dans la base de données

        // Récupération des clubs
        $clubA = $clubRepo->find($formData['clubA']);
        $clubB = $clubRepo->find($formData['clubB']);

        if (!$clubA || !$clubB) {
            throw $this->createNotFoundException('Clubs invalides.');
        }

        // Récupération des capitaines et de l'arbitre
        $capitaineA = $userRepo->find($formData['capitaineA']);
        $capitaineB = $userRepo->find($formData['capitaineB']);
        $arbitre = $userRepo->find($formData['arbitre']);

        if (!$capitaineA || !$capitaineB || !$arbitre) {
            throw $this->createNotFoundException('Capitaines ou arbitre invalides.');
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
        int $id,
        Request $request,
        FeuilleMatchRepository $feuilleMatchRepo,
        EntityManagerInterface $em,
        ClubRepository $clubRepository,
        UserRepository $userRepo
    ): Response {
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
            $feuilleMatch->setType($formData['division'] ?? $feuilleMatch->getType());
            $feuilleMatch->setGroupe($formData['groupe'] ?? $feuilleMatch->getGroupe());
            $feuilleMatch->setInterclub($formData['interclub'] ?? $feuilleMatch->getInterclub());
            $feuilleMatch->setDateMatch(new \DateTimeImmutable($formData['dateMatch'] ?? $feuilleMatch->getDateMatch()->format('Y-m-d')));

            // Mise à jour des joueurs
            $joueurs = [];

            // Je boucle sur les joueurs A pour les ajouter à la feuille de match
            foreach ($formData['joueursA'] as $index => $joueurA) {
                $joueurs[] = [
                    'joueurA' => $joueurA,
                    'resultat' => $formData['resultats'][$index] ?? null,
                    'joueurB' => $formData['joueursB'][$index] ?? null,
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

        $capitainesEtArbitre = [
            'capitaineA' => null,
            'capitaineB' => null,
            'arbitre' => null,
        ];

        foreach ($feuilleMatch->getJoueurs() as $joueur) {
            if (isset($joueur['role']) && $joueur['role'] === 'capitaineA') {
                $capitainesEtArbitre['capitaineA'] = $userRepo->find($joueur['id']);
            } elseif (isset($joueur['role']) && $joueur['role'] === 'capitaineB') {
                $capitainesEtArbitre['capitaineB'] = $userRepo->find($joueur['id']);
            } elseif (isset($joueur['role']) && $joueur['role'] === 'arbitre') {
                $capitainesEtArbitre['arbitre'] = $userRepo->find($joueur['id']);
            }
        }

        $clubs = $clubRepository->findAll();
        $joueursA = $feuilleMatch->getClubA() ? $feuilleMatch->getClubA()->getUsers() : [];
        $joueursB = $feuilleMatch->getClubB() ? $feuilleMatch->getClubB()->getUsers() : [];

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
        // 1. Récupération de la feuille de match
        $feuilleMatch = $feuilleMatchRepo->find($id);

        if (!$feuilleMatch) {
            throw $this->createNotFoundException('Feuille de match introuvable');
        }

        // 2. Récupération des clubs
        $clubA = $feuilleMatch->getClubA();
        $clubB = $feuilleMatch->getClubB();

        // 3. Préparation des joueurs et résultats (comme dans show)
        $joueursA = [];
        $joueursB = [];
        $resultats = [];

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

        // 4. Récupération des capitaines et de l'arbitre
        $capitainesEtArbitre = [
            'capitaineA' => null,
            'capitaineB' => null,
            'arbitre'    => null,
        ];

        foreach ($feuilleMatch->getJoueurs() as $joueur) {
            if (isset($joueur['role']) && $joueur['role'] === 'capitaineA') {
                $capitainesEtArbitre['capitaineA'] = $userRepo->find($joueur['id']);
            } elseif (isset($joueur['role']) && $joueur['role'] === 'capitaineB') {
                $capitainesEtArbitre['capitaineB'] = $userRepo->find($joueur['id']);
            } elseif (isset($joueur['role']) && $joueur['role'] === 'arbitre') {
                $capitainesEtArbitre['arbitre'] = $userRepo->find($joueur['id']);
            }
        }

        // 5. Génération du contenu HTML pour le PDF
        $html = $this->renderView('pages/match_sheets/pdf.html.twig', [
            'feuilleMatch'        => $feuilleMatch,
            'clubA'               => $clubA,
            'clubB'               => $clubB,
            'joueursA'            => $joueursA,
            'joueursB'            => $joueursB,
            'resultats'           => $resultats,
            'capitainesEtArbitre' => $capitainesEtArbitre,
        ]);

        // 6. Configuration de Dompdf
        $options = new Options();
        $options->set('defaultFont', 'Arial');

        // Pour autoriser le chargement de ressources externes (CDN) :
        // (ex: tailwind via CDN)
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // 7. Retourne le fichier PDF en téléchargement
        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="feuille_de_match_' . $feuilleMatch->getId() . '.pdf"',
            ]
        );
    }
}
