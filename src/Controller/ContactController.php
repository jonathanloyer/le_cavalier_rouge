<?php

namespace App\Controller;

use App\Service\MongoDBClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{

    #[Route('/contactez-nous', name: 'public_contact_form', methods: ['GET', 'POST'])]
    public function publicContact(Request $request, MongoDBClient $mongoDBClient): Response
    {
        // Aucune vérification d'accès ici
        $db = $mongoDBClient->getDatabase();
    
        // Logique pour le formulaire
        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');
            $email = $request->request->get('email');
            $message = $request->request->get('message');
    
            if (empty($name) || empty($email) || empty($message)) {
                $this->addFlash('error', 'Tous les champs doivent être remplis.');
            } else {
                $db->contacts->insertOne([
                    'name' => $name,
                    'email' => $email,
                    'message' => $message,
                    'createdAt' => date('c'),
                ]);
    
                $this->addFlash('success', 'Votre message a été envoyé avec succès.');
                return $this->redirectToRoute('public_contact_form');
            }
        }
    
        return $this->render('pages/contact/contact_form.html.twig');
    }
    


    #[Route('/admin/contacts', name: 'admin_manage_contacts', methods: ['GET', 'POST'])]
    public function contact(Request $request, MongoDBClient $mongoDBClient): Response
    {
        // 1. Récupération de la base de données MongoDB via le service MongoDBClient.
        $db = $mongoDBClient->getDatabase();

        // 2. Gestion de la requête POST pour traiter les données du formulaire.
        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');
            $email = $request->request->get('email');
            $message = $request->request->get('message');

            // 3. Validation basique des données pour éviter les champs vides.
            if (empty($name) || empty($email) || empty($message)) {
                $this->addFlash('error', 'Tous les champs doivent être remplis.');
            } else {
                // 4. Insertion des données dans la collection "contacts".
                $db->contacts->insertOne([
                    'name' => $name,
                    'email' => $email,
                    'message' => $message,
                    'createdAt' => date('c'), // Format ISO 8601
                ]);

                $this->addFlash('success', 'Votre message a été envoyé avec succès.');
                return $this->redirectToRoute('admin_manage_contacts');
            }
        }

        // 5. Récupération de tous les contacts depuis la base de données MongoDB.
        $contacts = $db->contacts->find()->toArray();

        // 6. Conversion des BSONDocument en tableaux et traitement des données.
        $contacts = array_map(function ($contact) {
            $contactArray = $contact->getArrayCopy();

            // Gestion de l'identifiant `_id` en tant que chaîne.
            if (isset($contactArray['_id']) && method_exists($contactArray['_id'], '__toString')) {
                $contactArray['_id'] = (string) $contactArray['_id'];
            }

            // Gestion de `createdAt` en tant que chaîne ou null.
            if (isset($contactArray['createdAt']) && is_string($contactArray['createdAt'])) {
                try {
                    $contactArray['createdAt'] = new \DateTime($contactArray['createdAt']);
                } catch (\Exception $e) {
                    $contactArray['createdAt'] = null; // Valeur par défaut en cas d'erreur
                }
            } else {
                $contactArray['createdAt'] = null; // Valeur par défaut si non défini ou invalide
            }

            return $contactArray;
        }, $contacts);

        // 7. Rendu de la vue Twig avec les contacts passés en contexte.
        return $this->render('pages/admin/manage_contacts.html.twig', [
            'contacts' => $contacts,
        ]);
    }

    #[Route('/admin/contacts/delete/{id}', name: 'admin_delete_contact', methods: ['POST'])]
    public function deleteContact(string $id, MongoDBClient $mongoDBClient, Request $request): Response
    {
        // Vérifiez le token CSRF pour sécuriser la suppression.
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete_contact' . $id, $token)) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('admin_manage_contacts');
        }

        // Récupération de la base de données MongoDB.
        $db = $mongoDBClient->getDatabase();

        // Suppression du contact par son identifiant (converti en chaîne).
        $db->contacts->deleteOne(['_id' => $id]);

        $this->addFlash('success', 'Le contact a été supprimé avec succès.');
        return $this->redirectToRoute('admin_manage_contacts');
    }
}
