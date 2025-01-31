<?php 
namespace App\Tests\Entity;

use App\Entity\FeuilleMatch;
use App\Entity\Club;
use PHPUnit\Framework\TestCase;

class FeuilleMatchTest extends TestCase
{
    public function testClubAGetterAndSetter(): void
    {
        $clubA = new Club();
        $match = new FeuilleMatch();
        $match->setClubA($clubA);
        $this->assertSame($clubA, $match->getClubA());
    }

    public function testClubBGetterAndSetter(): void
    {
        $clubB = new Club();
        $match = new FeuilleMatch();
        $match->setClubB($clubB);
        $this->assertSame($clubB, $match->getClubB());
    }

    public function testDateMatchGetterAndSetter(): void
    {
        $date = new \DateTimeImmutable();
        $match = new FeuilleMatch();
        $match->setDateMatch($date);
        $this->assertSame($date, $match->getDateMatch());
    }

    public function testJoueursSetterAndGetter(): void
    {
        $joueurs = [
            ['id' => 1, 'role' => 'Captain', 'resultat' => 'Win'],
            ['id' => 2, 'role' => 'Player', 'resultat' => 'Lose'],
        ];

        $match = new FeuilleMatch();
        $match->setJoueurs($joueurs);

        $this->assertCount(2, $match->getJoueurs());
        $this->assertEquals('Captain', $match->getJoueurs()[0]['role']);
    }
}
