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
use Dompdf\Dompdf;
use Dompdf\Options;

class MatchSheetsController extends AbstractController
{
    // Je crée une route pour la page des feuilles de match
    #[Route('/feuille-de-match', name: 'app_match_sheets')]
    public function index(): Response
    {
        return $this->render('pages/match_sheets/matchsheets.html.twig');
    }

    // Je crée une route pour créer une feuille de match
    #[Route('/feuille-de-match/creer/{type}', name: 'app_match_sheets_create', defaults: ['type' => null])]
    public function create(?string $type, ClubRepository $clubRepository): Response
    {
        // Vérification des rôles
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_CAPITAINE')) {
            throw $this->createAccessDeniedException('Accès refusé.');
        }

        // Utilisation du type transmis par la route ou définition d'une valeur par défaut
        if (!in_array($type, ['criterium', 'national'])) {
            $type = 'criterium'; // Par défaut si le type est invalide ou non transmis
        }

        // Récupération des clubs
        $clubs = $clubRepository->findAll();

        // Rendu de la vue avec les clubs
        return $this->render('pages/match_sheets/create.html.twig', [
            'type' => $type,
            'clubs' => $clubs,
        ]);
    }

    // Je crée une route pour enregistrer une feuille de match
    #[Route('/feuille-de-match/save', name: 'app_match_sheets_save', methods: ['POST'])]
    public function save(Request $request, EntityManagerInterface $em, ClubRepository $clubRepository): Response
    {
        $formData = $request->request->all();
    
        // Récupérez les IDs des clubs
        $clubAId = $formData['clubA'] ?? null;
        $clubBId = $formData['clubB'] ?? null;
    
        // Vérifiez que les IDs sont valides
        if (!$clubAId || !$clubBId) {
            throw new \Exception('Les identifiants des clubs sont invalides ou manquants.');
        }
    
        // Recherchez les entités Club à partir des IDs
        $clubA = $clubRepository->find($clubAId);
        $clubB = $clubRepository->find($clubBId);
    
        if (!$clubA || !$clubB) {
            throw new \Exception('Un des clubs sélectionnés est introuvable dans la base de données.');
        }
    
        // Je crée la feuille de match
        $feuilleMatch = new FeuilleMatch();

        // Je définis les valeurs de la feuille de match
        $feuilleMatch->setClubA($clubA);
        $feuilleMatch->setClubB($clubB);
        $feuilleMatch->setRonde((int)($formData['ronde'] ?? 1)); // Par défaut, définissez une valeur pour la ronde

        // Je définis la date du match
        $feuilleMatch->setDateMatch(new \DateTimeImmutable($formData['dateMatch'] ?? 'now'));

        // Je définis la date de création de la feuille de match
        $feuilleMatch->setCreation(new \DateTimeImmutable());
    
        // J'enregistre la feuille de match
        $em->persist($feuilleMatch);

        // J'envoie les données en base de données
        $em->flush();
    
        // Je redirige l'utilisateur vers la liste des feuilles de match
        return $this->redirectToRoute('app_match_sheets_list');
    }
    
    // Je crée une route pour la liste des feuilles de match
    #[Route('/feuille-de-match/consulter', name: 'app_match_sheets_list')]
    public function list(FeuilleMatchRepository $feuilleMatchRepo): Response
    {
        // Récupérer toutes les feuilles de match
        $feuilles = $feuilleMatchRepo->findAll();

        // Rendu de la vue avec les feuilles de match
        return $this->render('pages/match_sheets/list.html.twig', [
            'feuilles' => $feuilles,
        ]);
    }

    // Je crée une route pour afficher une feuille de match
    #[Route('/feuille-de-match/{id}', name: 'app_match_sheets_show')]
    public function show($id, FeuilleMatchRepository $feuilleMatchRepo): Response
    {
        // Je récupère la feuille de match
        $feuilleMatch = $feuilleMatchRepo->find($id);

        // Je vérifie que la feuille de match existe
        // dd($feuilleMatch);

        // Si la feuille de match n'existe pas, je renvoie une erreur 404
        if (!$feuilleMatch) {
            $this->addFlash('error', "La feuille de match avec l'ID $id est introuvable.");
    return $this->redirectToRoute('app_match_sheets_list');
        }

        // Rendu de la vue avec la feuille de match
        return $this->render('pages/match_sheets/show.html.twig', [
            'feuilleMatch' => $feuilleMatch,
        ]);
    }

    // Je crée une route pour récupérer les joueurs d'un club
    #[Route('/feuille-de-match/club/{id}/joueurs', name: 'app_match_sheets_get_players_by_club', methods: ['GET'])]
    public function getPlayersByClub(int $id, ClubRepository $clubRepository): Response
    {
        // Récupérer le club
        $club = $clubRepository->find($id);

        // Si le club n'existe pas, renvoyer une erreur 404
        if (!$club) {
            return $this->json(['error' => 'Club introuvable'], Response::HTTP_NOT_FOUND);
        }

        // Récupére les utilisateurs actifs liés au club
        $joueurs = $club->getUsers()->filter(function ($user) {

            // Filtre uniquement les utilisateurs actifs
            return $user->getActive(); 
        });

        // Transforme les données en format JSON
        $data = array_map(function ($joueur) {
            return [
                'id' => $joueur->getId(), // ID de l'utilisateur
                'codeFfe' => $joueur->getCodeFFE(), // Code FFE de l'utilisateur
                'firstName' => $joueur->getFirstName(), // Prénom de l'utilisateur
                'lastName' => $joueur->getLastName(), // Nom de l'utilisateur
            ];

            // Transforme les données en tableau
        }, $joueurs->toArray());

        // Renvoie les données en format JSON
        return $this->json($data);
    }

    // Je crée une route pour télécharger une feuille de match au format PDF
    #[Route('/feuille-de-match/{id}/pdf', name: 'app_match_sheets_download_pdf')]
    public function downloadPdf($id, FeuilleMatchRepository $feuilleMatchRepo): Response
    {
        // Je récupère la feuille de match
        $feuilleMatch = $feuilleMatchRepo->find($id);

        // Si la feuille de match n'existe pas, je renvoie une erreur 404
        if (!$feuilleMatch) {
            throw $this->createNotFoundException("Feuille de match introuvable.");
        }

        // Je crée le contenu HTML de la feuille de match
        $html = $this->renderView('pages/match_sheets/pdf.html.twig', [
            'feuilleMatch' => $feuilleMatch,
        ]);

        // Je crée une instance de Dompdf
        $options = new Options();

        // Je définis la police par défaut
        $options->set('defaultFont', 'Arial');

        // Je crée une instance de Dompdf
        $dompdf = new Dompdf($options);

        // Je charge le contenu HTML
        $dompdf->loadHtml($html);

        // Je rends le PDF
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Je renvoie le PDF en réponse
        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="feuille_de_match_' . $feuilleMatch->getId() . '.pdf"',
        ]);
    }

    // Je crée une route pour modifier une feuille de match
    #[Route('/feuille-de-match/{id}/modifier', name: 'app_match_sheets_edit')]
    public function edit($id, Request $request, FeuilleMatchRepository $feuilleMatchRepo, ClubRepository $clubRepository): Response
    {
        $feuilleMatch = $feuilleMatchRepo->find($id);
        if (!$feuilleMatch) {
            throw $this->createNotFoundException("Feuille de match introuvable.");
        }
    
        $clubs = $clubRepository->findAll();
    
        // Exemples de résultats possibles (adapter selon vos besoins)
        $resultats = [
            'Gain Blanc',
            'Gain Noir',
            'Nulle',
        ];
    
        return $this->render('pages/match_sheets/edit.html.twig', [
            'feuilleMatch' => $feuilleMatch,
            'clubs' => $clubs,
            'resultats' => $resultats,
        ]);
    }
    // Je crée une route pour supprimer une feuille de match
    #[Route('/feuille-de-match/{id}/supprimer', name: 'app_match_sheets_delete', methods: ['POST'])]
    public function delete($id, FeuilleMatchRepository $feuilleMatchRepo, EntityManagerInterface $em): Response
    {
        // Je récupère la feuille de match par son ID
        $feuilleMatch = $feuilleMatchRepo->find($id);

        // Si la feuille de match n'existe pas, je renvoie une erreur 404
        if ($feuilleMatch) {
            $em->remove($feuilleMatch);
            $em->flush();
        }

        // Je redirige l'utilisateur vers la liste des feuilles de match
        return $this->redirectToRoute('app_match_sheets_list');
    }
}
