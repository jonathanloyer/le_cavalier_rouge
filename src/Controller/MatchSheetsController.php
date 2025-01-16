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
    #[Route('/feuille-de-match', name: 'app_match_sheets')]
    public function index(): Response
    {
        return $this->render('pages/match_sheets/matchsheets.html.twig');
    }

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

    #[Route('/feuille-de-match/save', name: 'app_match_sheets_save', methods: ['POST'])]
    public function save(Request $request, EntityManagerInterface $em): Response
    {
        $formData = $request->request->all();

        $feuilleMatch = new FeuilleMatch();
        $feuilleMatch->setClubA($formData['clubA'] ?? null);
        $feuilleMatch->setClubB($formData['clubB'] ?? null);
        $feuilleMatch->setRonde($formData['ronde'] ?? null);
        $feuilleMatch->setDateMatch(new \DateTimeImmutable($formData['dateMatch'] ?? 'now'));
        $feuilleMatch->setCreation(new \DateTimeImmutable());

        $em->persist($feuilleMatch);
        $em->flush();

        return $this->redirectToRoute('app_match_sheets_list');
    }

    #[Route('/feuille-de-match/consulter', name: 'app_match_sheets_list')]
    public function list(FeuilleMatchRepository $feuilleMatchRepo): Response
    {
        $feuilles = $feuilleMatchRepo->findAll();

        return $this->render('pages/match_sheets/list.html.twig', [
            'feuilles' => $feuilles,
        ]);
    }

    #[Route('/feuille-de-match/{id}', name: 'app_match_sheets_show')]
    public function show($id, FeuilleMatchRepository $feuilleMatchRepo): Response
    {
        $feuilleMatch = $feuilleMatchRepo->find($id);

        if (!$feuilleMatch) {
            throw $this->createNotFoundException("Feuille de match introuvable avec l'ID :" . $id);
        }

        return $this->render('pages/match_sheets/show.html.twig', [
            'feuilleMatch' => $feuilleMatch,
        ]);
    }

    #[Route('/feuille-de-match/club/{id}/joueurs', name: 'app_match_sheets_get_players_by_club', methods: ['GET'])]
    public function getPlayersByClub(int $id, ClubRepository $clubRepository): Response
    {
        // Récupérer le club
        $club = $clubRepository->find($id);

        if (!$club) {
            return $this->json(['error' => 'Club introuvable'], Response::HTTP_NOT_FOUND);
        }

        // Récupérer les utilisateurs actifs liés au club
        $joueurs = $club->getUsers()->filter(function ($user) {
            return $user->getActive(); // Filtrer uniquement les utilisateurs actifs
        });

        // Transformer les données en format JSON
        $data = array_map(function ($joueur) {
            return [
                'id' => $joueur->getId(),
                'codeFfe' => $joueur->getCodeFFE(),
                'firstName' => $joueur->getFirstName(),
                'lastName' => $joueur->getLastName(),
            ];
        }, $joueurs->toArray());

        return $this->json($data);
    }

    #[Route('/feuille-de-match/{id}/pdf', name: 'app_match_sheets_download_pdf')]
    public function downloadPdf($id, FeuilleMatchRepository $feuilleMatchRepo): Response
    {
        $feuilleMatch = $feuilleMatchRepo->find($id);

        if (!$feuilleMatch) {
            throw $this->createNotFoundException("Feuille de match introuvable.");
        }

        $html = $this->renderView('pages/match_sheets/pdf.html.twig', [
            'feuilleMatch' => $feuilleMatch,
        ]);

        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($options);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="feuille_de_match_' . $feuilleMatch->getId() . '.pdf"',
        ]);
    }

    #[Route('/feuille-de-match/{id}/modifier', name: 'app_match_sheets_edit')]
    public function edit($id, Request $request, FeuilleMatchRepository $feuilleMatchRepo, EntityManagerInterface $em): Response
    {
        $feuilleMatch = $feuilleMatchRepo->find($id);

        if (!$feuilleMatch) {
            throw $this->createNotFoundException("Feuille de match introuvable.");
        }

        if ($request->isMethod('POST')) {
            $formData = $request->request->all();
            $feuilleMatch->setClubA($formData['clubA'] ?? null);
            $feuilleMatch->setClubB($formData['clubB'] ?? null);
            $feuilleMatch->setRonde($formData['ronde'] ?? null);
            $feuilleMatch->setDateMatch(new \DateTimeImmutable($formData['dateMatch'] ?? 'now'));

            $em->persist($feuilleMatch);
            $em->flush();

            return $this->redirectToRoute('app_match_sheets_show', ['id' => $id]);
        }

        return $this->render('pages/match_sheets/edit.html.twig', [
            'feuilleMatch' => $feuilleMatch,
        ]);
    }

    #[Route('/feuille-de-match/{id}/supprimer', name: 'app_match_sheets_delete', methods: ['POST'])]
    public function delete($id, FeuilleMatchRepository $feuilleMatchRepo, EntityManagerInterface $em): Response
    {
        $feuilleMatch = $feuilleMatchRepo->find($id);

        if ($feuilleMatch) {
            $em->remove($feuilleMatch);
            $em->flush();
        }

        return $this->redirectToRoute('app_match_sheets_list');
    }
}
