<?php 

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MatchSheetsController extends AbstractController
{
    #[Route('/feuille-de-match', name: 'app_match_sheets')]
    public function index(): Response
    {
        return $this->render('pages/match_sheets/matchsheets.html.twig');
    }

    #[Route('/feuille-de-match/creer', name: 'app_match_sheets_create')]
    public function create(): Response
    {
        
        return $this->render('pages/match_sheets/create.html.twig');
    }

    #[Route('/feuille-de-match/{id}', name: 'app_match_sheets_show')]
    public function show($id): Response
    {
        return $this->render('pages/match_sheets/show.html.twig', [
            'id' => $id,
        ]);
    }

    #[Route('/feuille-de-match/{id}/modifier', name: 'app_match_sheets_edit')]
    public function edit($id): Response
    {
        return $this->render('pages/match_sheets/edit.html.twig', [
            'id' => $id,
        ]);
    }

    #[Route('/feuille-de-match/{id}/supprimer', name: 'app_match_sheets_delete')]
    public function delete($id): Response
    {
        return $this->render('pages/match_sheets/delete.html.twig', [
            'id' => $id,
        ]);
    }
}