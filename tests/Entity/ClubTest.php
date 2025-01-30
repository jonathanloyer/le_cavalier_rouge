<?php 
namespace App\Tests\Entity;

use App\Entity\Club;
use App\Entity\Joueurs;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class ClubTest extends TestCase
{
    public function testNameGetterAndSetter(): void
    {
        $club = new Club();
        $club->setName('Chess Club');
        $this->assertEquals('Chess Club', $club->getName());
    }

    public function testAddAndGetJoueurs(): void
    {
        $joueur = new Joueurs();
        $club = new Club();
        $club->addJoueur($joueur);

        $this->assertCount(1, $club->getJoueurs());
        $this->assertSame($joueur, $club->getJoueurs()[0]);
    }

    public function testAddAndGetUsers(): void
    {
        $user = new User();
        $club = new Club();
        $club->addUser($user);

        $this->assertCount(1, $club->getUsers());
        $this->assertSame($user, $club->getUsers()[0]);
    }
}
