<?php

namespace App\DataFixtures;

use App\Entity\Joueurs;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class JoueursFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $joueur = new Joueurs();
            $joueur->setName("Joueur $i")
                   ->setClub($this->getReference("club_" . rand(1, 5)));

            $manager->persist($joueur);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ClubFixture::class,
        ];
    }
}