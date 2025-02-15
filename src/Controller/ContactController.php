<?php

namespace App\Controller;

use App\Document\Contact;
use App\Form\ContactType;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

class ContactController extends AbstractController
{
    #[Route('/contactez-nous', name: 'public_contact_form', methods: ['GET', 'POST'])]
    public function publicContact(Request $request, DocumentManager $dm, LoggerInterface $logger): Response
    {
        // Créer une instance de Contact
        $contact = new Contact();

        // Créer le formulaire en utilisant le formulaire ContactType
        $form = $this->createForm(ContactType::class, $contact);

        // Traitement du formulaire
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            // Validation du formulaire et enregistrement des données
            if ($form->isSubmitted() && $form->isValid()) {
                $contact->setCreatedAt(new \DateTime());

                // Persister l'objet Contact dans la base de données MongoDB
                $dm->persist($contact);
                $dm->flush();

                //la methode addFlash() permet d'ajouter un message flash
                $this->addFlash('success', 'Votre message a été envoyé avec succès.');

                // Enregistrement d'un message dans les logs afin de tracer l'envoi de message ce qui permet de savoir qui a envoyé le message
                $logger->info("Nouveau message de contact envoyé par : " . $contact->getEmail());

                // retourne à la page d'accueil
                return $this->redirectToRoute('app_home');
            }
        }

        return $this->render('pages/contact/contact_form.html.twig', [
            'form' => $form->createView(),  // Passer la vue du formulaire à Twig
        ]);
    }

    #[Route('/admin/contacts', name: 'admin_manage_contacts', methods: ['GET', 'POST'])]
    public function manageContacts(Request $request, DocumentManager $dm): Response
    {
        // Créer une instance de Contact
        $contact = new Contact();

        // Créer le formulaire en utilisant le formulaire ContactType
        $form = $this->createForm(ContactType::class, $contact);

        // Traitement du formulaire
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            // Validation du formulaire et enregistrement des données
            if ($form->isSubmitted() && $form->isValid()) {
                $contact->setCreatedAt(new \DateTime());

                // Persister l'objet Contact dans la base de données MongoDB
                $dm->persist($contact);
                $dm->flush();

                $this->addFlash('success', 'Votre message a été enregistré avec succès.');
                return $this->redirectToRoute('admin_manage_contacts');
            }
        }

        // Récupérer tous les contacts depuis MongoDB
        $contacts = $dm->getRepository(Contact::class)->findAll();

        return $this->render('pages/admin/manage_contacts.html.twig', [
            'contacts' => $contacts,
            'form' => $form->createView(), // Passer la vue du formulaire à Twig
        ]);
    }

    #[Route('/admin/contacts/delete/{id}', name: 'admin_delete_contact', methods: ['POST'])]
    public function deleteContact(string $id, DocumentManager $dm, Request $request, LoggerInterface $logger): Response
    {
        // Vérification du token CSRF
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete_contact' . $id, $token)) {
            $this->addFlash('error', 'Token CSRF invalide.');
            $logger->warning("CSRF token invalide pour ID: " . $id);
            return $this->redirectToRoute('admin_manage_contacts');
        }

        // Récupération et suppression du document
        $contact = $dm->getRepository(Contact::class)->find($id);

        if (!$contact) {
            $this->addFlash('error', 'Aucun message trouvé avec cet identifiant.');
            $logger->warning("Aucun contact trouvé pour l'ID: " . $id);
            return $this->redirectToRoute('admin_manage_contacts');
        }

        $dm->remove($contact);
        $dm->flush();

        $this->addFlash('success', 'Le message a été supprimé avec succès.');
        $logger->info("Contact supprimé avec succès: " . $id);

        return $this->redirectToRoute('admin_manage_contacts');
    }
}
