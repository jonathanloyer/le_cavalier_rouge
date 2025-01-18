<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    // Je crée une route pour la page d'accueil
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        // J'affiche la vue de la page d'accueil
        return $this->render('pages/home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
}
