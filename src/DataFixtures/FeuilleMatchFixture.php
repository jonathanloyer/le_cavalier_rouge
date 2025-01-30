<?php

namespace App\DataFixtures;

use App\Entity\FeuilleMatch;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class FeuilleMatchFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $match = new FeuilleMatch();
            $match->setClubA($this->getReference("club_" . rand(1, 5)))
                ->setClubB($this->getReference("club_" . rand(1, 5)))
                ->setDateMatch(new \DateTimeImmutable("2025-0$i-10"))
                ->setCreation(new \DateTimeImmutable())
                ->setRonde(rand(1, 5))
                ->setType('criterium')
                ->setGroupe('Groupe ' . rand(1, 2))
                ->setInterclub('Interclub Jeune')
                ->setJoueurs([
                    ['id' => rand(1, 10), 'role' => 'Player', 'resultat' => 'Win'],
                ]);

            $manager->persist($match);
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