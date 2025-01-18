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
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Accès réservé aux administrateurs.');

        $competition = new Competitions();
        $form = $this->createForm(CompetitionType::class, $competition);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $competition->setStatus('En attente');
            $em->persist($competition);
            $em->flush();

            $this->addFlash('success', 'Compétition ajoutée avec succès.');
            return $this->redirectToRoute('admin_manage_competitions');
        }

        $competitions = $competitionsRepository->findBy([], ['competitionDate' => 'ASC']);

        return $this->render('pages/admin/manage_competitions.html.twig', [
            'form' => $form->createView(),
            'competitions' => $competitions,
        ]);
    }

    #[Route('/admin/competitions/save', name: 'admin_save_competition', methods: ['POST'])]
    public function save(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Accès réservé aux administrateurs.');

        $data = $request->request->all();
        $competition = new Competitions();
        $competition->setName($data['nom']);
        $competition->setStatus('En attente');

        if (isset($data['competitionDate'])) {
            try {
                $competitionDate = new \DateTime($data['competitionDate']);
                $competition->setCompetitionDate($competitionDate);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Date invalide. Veuillez réessayer.');
                return $this->redirectToRoute('admin_manage_competitions');
            }
        }

        $em->persist($competition);
        $em->flush();

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
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Accès réservé aux administrateurs.');

        $competition = $competitionsRepository->find($id);

        if (!$competition) {
            $this->addFlash('error', 'Compétition introuvable.');
            return $this->redirectToRoute('admin_manage_competitions');
        }

        $form = $this->createForm(CompetitionType::class, $competition);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Compétition modifiée avec succès.');
            return $this->redirectToRoute('admin_manage_competitions');
        }

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
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Accès réservé aux administrateurs.');

        $competition = $competitionsRepository->find($id);

        if (!$competition) {
            $this->addFlash('error', 'Compétition introuvable.');
            return $this->redirectToRoute('admin_manage_competitions');
        }

        $em->remove($competition);
        $em->flush();

        $this->addFlash('success', 'Compétition supprimée avec succès.');
        return $this->redirectToRoute('admin_manage_competitions');
    }

    #[Route('/profile/competitions', name: 'user_competitions_list', methods: ['GET'])]
    public function listCompetitionsForUser(CompetitionsRepository $competitionsRepository): Response
    {
        $competitions = $competitionsRepository->findBy([], ['competitionDate' => 'ASC']);

        return $this->render('pages/profile/competitions.html.twig', [
            'competitions' => $competitions,
        ]);
    }
}
