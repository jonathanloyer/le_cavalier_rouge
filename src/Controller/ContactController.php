<?php

namespace App\Controller;

use App\Service\MongoDBClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'contact_form', methods: ['GET', 'POST'])]
    public function contact(Request $request, MongoDBClient $mongoDBClient): Response
    {
        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');
            $email = $request->request->get('email');
            $message = $request->request->get('message');

            // Utilisation de getDatabase pour interagir avec MongoDB
            $db = $mongoDBClient->getDatabase();
            $db->contacts->insertOne([
                'name' => $name,
                'email' => $email,
                'message' => $message,
                'createdAt' => new \DateTime(),
            ]);

            $this->addFlash('success', 'Votre message a été envoyé avec succès.');
            return $this->redirectToRoute('contact_form');
        }

        return $this->render('pages/contact/contact_form.html.twig');
    }
}
