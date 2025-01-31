<?php

namespace App\DataFixtures;

use App\Entity\Club;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ClubFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $club = new Club();
            $club->setName("Club $i");
            $manager->persist($club);

            // Ajout d'une référence pour les autres fixtures
            $this->addReference("club_$i", $club);
        }

        $manager->flush();
    }
}