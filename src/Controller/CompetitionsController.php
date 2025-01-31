<?php

namespace App\Controller;

use App\Entity\Competitions;
use App\Form\CompetitionType;
use App\Repository\CompetitionsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CompetitionsController extends AbstractController
{
    #[Route('/admin/competitions', name: 'admin_manage_competitions', methods: ['GET', 'POST'])]
    public function manageCompetitions(
        Request $request,
        CompetitionsRepository $competitionsRepository,
        EntityManagerInterface $em
    ): Response {
        // Je vérifie que seul un administrateur peut accéder à cette page.
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Accès réservé aux administrateurs.');

        // Je crée une nouvelle entité Competitions.
        $competition = new Competitions();
        // Je génère le formulaire pour ajouter une compétition.
        $form = $this->createForm(CompetitionType::class, $competition);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Je définis le statut par défaut de la compétition à "En attente".
            $competition->setStatus('En attente');
            // J'enregistre la compétition en base de données.
            $em->persist($competition);
            $em->flush();

            // J'ajoute un message de succès pour informer l'utilisateur.
            $this->addFlash('success', 'Compétition ajoutée avec succès.');
            return $this->redirectToRoute('admin_manage_competitions');
        }

        // Je récupère toutes les compétitions triées par date.
        $competitions = $competitionsRepository->findBy([], ['competitionDate' => 'ASC']);

        // Je retourne la vue pour gérer les compétitions avec le formulaire et la liste des compétitions.
        return $this->render('pages/admin/manage_competitions.html.twig', [
            'form' => $form->createView(),
            'competitions' => $competitions,
        ]);
    }

    #[Route('/admin/competitions/save', name: 'admin_save_competition', methods: ['POST'])]
    public function save(Request $request, EntityManagerInterface $em): Response
    {
        // Je vérifie que seul un administrateur peut effectuer cette action.
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Accès réservé aux administrateurs.');

        // Je récupère les données envoyées via la requête.
        $data = $request->request->all();
        // Je crée une nouvelle entité Competitions.
        $competition = new Competitions();
        $competition->setName($data['nom']);
        $competition->setStatus('En attente');

        if (isset($data['competitionDate'])) {
            try {
                // Je tente de convertir la date reçue en un objet DateTime.
                $competitionDate = new \DateTime($data['competitionDate']);
                $competition->setCompetitionDate($competitionDate);
            } catch (\Exception $e) {
                // J'ajoute un message d'erreur si la date est invalide.
                $this->addFlash('error', 'Date invalide. Veuillez réessayer.');
                return $this->redirectToRoute('admin_manage_competitions');
            }
        }

        // J'enregistre la compétition en base de données.
        $em->persist($competition);
        $em->flush();

        // J'ajoute un message de succès pour informer l'utilisateur.
        $this->addFlash('success', 'Compétition ajoutée avec succès.');
        return $this->redirectToRoute('admin_manage_competitions');
    }

    #[Route('/admin/competitions/{id}/modify', name: 'admin_modify_competition', methods: ['GET', 'POST'])]
    public function modify(
        int $id,
        Request $request,
        CompetitionsRepository $competitionsRepository,
        EntityManagerInterface $em
    ): Response {
        // Je vérifie que seul un administrateur peut accéder à cette page.
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Accès réservé aux administrateurs.');

        // Je récupère la compétition à modifier grâce à son ID.
        $competition = $competitionsRepository->find($id);

        if (!$competition) {
            // J'ajoute un message d'erreur si la compétition n'est pas trouvée.
            $this->addFlash('error', 'Compétition introuvable.');
            return $this->redirectToRoute('admin_manage_competitions');
        }

        // Je génère le formulaire pour modifier la compétition.
        $form = $this->createForm(CompetitionType::class, $competition);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Je mets à jour la compétition en base de données.
            $em->flush();

            // J'ajoute un message de succès pour informer l'utilisateur.
            $this->addFlash('success', 'Compétition modifiée avec succès.');
            return $this->redirectToRoute('admin_manage_competitions');
        }

        // Je retourne la vue pour modifier la compétition.
        return $this->render('pages/admin/edit_competition.html.twig', [
            'form' => $form->createView(),
            'competition' => $competition,
        ]);
    }

    #[Route('/admin/competitions/{id}/delete', name: 'admin_delete_competition', methods: ['POST'])]
    public function delete(
        int $id,
        CompetitionsRepository $competitionsRepository,
        EntityManagerInterface $em
    ): Response {
        // Je vérifie que seul un administrateur peut effectuer cette action.
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Accès réservé aux administrateurs.');

        // Je récupère la compétition à supprimer grâce à son ID.
        $competition = $competitionsRepository->find($id);

        if (!$competition) {
            // J'ajoute un message d'erreur si la compétition n'est pas trouvée.
            $this->addFlash('error', 'Compétition introuvable.');
            return $this->redirectToRoute('admin_manage_competitions');
        }

        // Je supprime la compétition de la base de données.
        $em->remove($competition);
        $em->flush();

        // J'ajoute un message de succès pour informer l'utilisateur.
        $this->addFlash('success', 'Compétition supprimée avec succès.');
        return $this->redirectToRoute('admin_manage_competitions');
    }

    #[Route('/profile/competitions', name: 'user_competitions_list', methods: ['GET'])]
    public function listCompetitionsForUser(CompetitionsRepository $competitionsRepository): Response
    {
        // Je récupère toutes les compétitions triées par date.
        $competitions = $competitionsRepository->findBy([], ['competitionDate' => 'ASC']);

        // Je retourne la vue pour afficher les compétitions disponibles pour l'utilisateur.
        return $this->render('pages/profile/competitions.html.twig', [
            'competitions' => $competitions,
        ]);
    }
}
