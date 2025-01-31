<?php 
namespace App\Tests\Entity;

use App\Entity\PlayerRole;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class PlayerRoleTest extends TestCase
{
    public function testRoleNameGetterAndSetter(): void
    {
        $role = new PlayerRole();
        $role->setRoleName('Captain');
        $this->assertEquals('Captain', $role->getRoleName());
    }

    public function testUserGetterAndSetter(): void
    {
        $user = new User();
        $role = new PlayerRole();
        $role->setUser($user);
        $this->assertSame($user, $role->getUser());
    }
}
