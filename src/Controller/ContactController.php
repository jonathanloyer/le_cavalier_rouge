<?php

namespace App\Controller;

use App\Document\Contact;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

class ContactController extends AbstractController
{
    #[Route('/contactez-nous', name: 'public_contact_form', methods: ['GET', 'POST'])]
    public function publicContact(Request $request, DocumentManager $dm): Response
    {
        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');
            $email = $request->request->get('email');
            $message = $request->request->get('message');

            // Validation des champs
            if (empty($name) || empty($email) || empty($message)) {
                $this->addFlash('error', 'Tous les champs doivent être remplis.');
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->addFlash('error', 'Adresse email invalide.');
            } elseif (strlen($name) > 100 || strlen($message) > 1000) {
                $this->addFlash('error', 'Le nom ou le message est trop long.');
            } else {
                $contact = new Contact();
                $contact->setName($name)
                        ->setEmail($email)
                        ->setMessage($message)
                        ->setCreatedAt(new \DateTime());

                $dm->persist($contact);
                $dm->flush();

                $this->addFlash('success', 'Votre message a été envoyé avec succès.');
                return $this->redirectToRoute('public_contact_form');
            }
        }

        return $this->render('pages/contact/contact_form.html.twig');
    }

    #[Route('/admin/contacts', name: 'admin_manage_contacts', methods: ['GET', 'POST'])]
    public function manageContacts(Request $request, DocumentManager $dm): Response
    {
        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');
            $email = $request->request->get('email');
            $message = $request->request->get('message');

            // Validation des champs
            if (empty($name) || empty($email) || empty($message)) {
                $this->addFlash('error', 'Tous les champs doivent être remplis.');
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->addFlash('error', 'Adresse email invalide.');
            } elseif (strlen($name) > 100 || strlen($message) > 1000) {
                $this->addFlash('error', 'Le nom ou le message est trop long.');
            } else {
                $contact = new Contact();
                $contact->setName($name)
                        ->setEmail($email)
                        ->setMessage($message)
                        ->setCreatedAt(new \DateTime());

                $dm->persist($contact);
                $dm->flush();

                $this->addFlash('success', 'Votre message a été enregistré avec succès.');
                return $this->redirectToRoute('admin_manage_contacts');
            }
        }

        // Récupération de tous les contacts depuis MongoDB
        $contacts = $dm->getRepository(Contact::class)->findAll();

        return $this->render('pages/admin/manage_contacts.html.twig', [
            'contacts' => $contacts,
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
