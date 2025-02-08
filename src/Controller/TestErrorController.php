<?php

namespace App\Controller;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestErrorController extends AbstractController
{
    #[Route('/test-error400/{id?}', name: 'test_error400')]
    public function testError400(?int $id = null): Response
    {
        if ($id === null) {
            throw new BadRequestHttpException("Requête invalide (400)");
        }

        return new Response("ID reçu : $id");
    }

    #[Route('/test-error500', name: 'test_error500')]
    public function testError500(): Response
    {
        throw new \Exception('Erreur interne du serveur (500)');
    }

    #[Route('/test-error-generic', name: 'test_error_generic')]
    public function testErrorGeneric(): Response
    {
        throw new ServiceUnavailableHttpException(null, "Service temporairement indisponible (503)");
    }

    #[Route('/test-error404', name: 'test_error404')]
    public function testError404(): Response
    {
        throw new NotFoundHttpException("Page non trouvée (404)");
    }
    
    #[Route('/test-error403', name: 'test_error403')]
    public function testError403(): Response
    {
        throw new AccessDeniedHttpException("Accès refusé (403)");
    }

    #[Route('/admin', name: 'admin_page')]
    public function admin(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return new Response("Page Admin");
    }
}
