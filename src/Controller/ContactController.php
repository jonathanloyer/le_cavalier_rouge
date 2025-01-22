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
        // Je vérifie si la requête est de type POST pour traiter le formulaire.
        if ($request->isMethod('POST')) {
            // Je récupère les données du formulaire envoyées par l'utilisateur.
            $name = $request->request->get('name');
            $email = $request->request->get('email');
            $message = $request->request->get('message');

            // J'utilise le service MongoDBClient pour obtenir la base de données MongoDB.
            $db = $mongoDBClient->getDatabase();
            // J'insère les données du formulaire dans la collection "contacts" de MongoDB.
            $db->contacts->insertOne([
                'name' => $name,
                'email' => $email,
                'message' => $message,
                'createdAt' => new \DateTime(), // J'ajoute une date de création pour le suivi.
            ]);

            // J'ajoute un message flash pour informer l'utilisateur que son message a été envoyé.
            $this->addFlash('success', 'Votre message a été envoyé avec succès.');
            // Je redirige l'utilisateur vers la page du formulaire après soumission.
            return $this->redirectToRoute('contact_form');
        }

        // Je retourne la vue du formulaire de contact si la requête n'est pas de type POST.
        return $this->render('pages/contact/contact_form.html.twig');
    }
}
