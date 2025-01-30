<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LegalController extends AbstractController
{
    #[Route('/cgu', name: 'cgu')]
    public function cgu(): Response
    {
        return $this->render('pages/legal/cgu.html.twig');
    }

    #[Route('/rgpd', name: 'rgpd')]
    public function rgpd(): Response
    {
        return $this->render('pages/legal/rgpd.html.twig');
    }

    #[Route('/mentions-legales', name: 'mentions_legales')]
    public function mentionsLegales(): Response
    {
        return $this->render('pages/legal/mentions_legales.html.twig');
    }
}
