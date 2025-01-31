<?php 
namespace App\Tests\Entity;

use App\Entity\Competitions;
use PHPUnit\Framework\TestCase;

class CompetitionsTest extends TestCase
{
    public function testNameGetterAndSetter(): void
    {
        $competition = new Competitions();
        $competition->setName('National League');
        $this->assertEquals('National League', $competition->getName());
    }

    public function testStatusGetterAndSetter(): void
    {
        $competition = new Competitions();
        $competition->setStatus('Completed');
        $this->assertEquals('Completed', $competition->getStatus());
    }

    public function testCompetitionDateGetterAndSetter(): void
    {
        $date = new \DateTime();
        $competition = new Competitions();
        $competition->setCompetitionDate($date);
        $this->assertSame($date, $competition->getCompetitionDate());
    }
}
