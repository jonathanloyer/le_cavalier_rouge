<?php 
namespace App\Tests\Entity;

use App\Entity\Joueurs;
use App\Entity\Club;
use PHPUnit\Framework\TestCase;

class JoueursTest extends TestCase
{
    public function testNameGetterAndSetter(): void
    {
        $joueur = new Joueurs();
        $joueur->setName('Jonathan');
        $this->assertEquals('Jonathan', $joueur->getName());
    }

    public function testClubGetterAndSetter(): void
    {
        $club = new Club();
        $joueur = new Joueurs();
        $joueur->setClub($club);
        $this->assertSame($club, $joueur->getClub());
    }
}
