<?php

namespace App\DataFixtures;

use App\Entity\Competitions;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CompetitionsFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 3; $i++) {
            $competition = new Competitions();
            $competition->setName("Competition $i")
                ->setStatus('En attente')
                ->setCompetitionDate(new \DateTime("2025-0$i-01"));
            $manager->persist($competition);

            // Ajout d'une référence pour les autres fixtures
            $this->addReference("competition_$i", $competition);
        }

        $manager->flush();
    }
}
