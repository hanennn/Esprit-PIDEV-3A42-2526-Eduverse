<?php
namespace App\Tests\Service;

use App\Entity\User;
use App\Service\UserManager;
use PHPUnit\Framework\TestCase;

class UserManagerTest extends TestCase
{
    public function testValidUser()
    {
        $user = new User();
        $user->setUsername('Yassine');
        $user->setNom('Aouadi');
        $user->setPrenom('Aouadi');
        $user->setEmail('mohamedyassine.aouadi@esprit.tn');
        $user->setPassword('123456789');

        $manager = new UserManager();
        $this->assertTrue($manager->validate($user));
    }

    public function testUserWithoutUsername()
    {
        $this->expectException(\InvalidArgumentException::class);

        $user = new User();
        $user->setNom('Yassine');
        $user->setPrenom('Aouadi');
        $user->setEmail('mohamedyassine.aouadi@esprit.tn');
        $user->setPassword('123456789');

        $manager = new UserManager();
        $manager->validate($user);
    }

    public function testUserWithoutNom()
    {
        $this->expectException(\InvalidArgumentException::class);

        $user = new User();
        $user->setUsername('Aouadi');
        $user->setPrenom('Yassine');
        $user->setEmail('mohamedyassine.aouadi@esprit.tn');
        $user->setPassword('123456789');

        $manager = new UserManager();
        $manager->validate($user);
    }

    public function testUserWithoutPrenom()
    {
        $this->expectException(\InvalidArgumentException::class);

        $user = new User();
        $user->setUsername('Aouadi');
        $user->setNom('Yassine');
        $user->setEmail('mohamedyassine.aouadi@esprit.tn');
        $user->setPassword('123456789');

        $manager = new UserManager();
        $manager->validate($user);
    }

    public function testUserWithoutEmail()
    {
        $this->expectException(\InvalidArgumentException::class);

        $user = new User();
        $user->setUsername('Aouadi');
        $user->setNom('Aouadi');
        $user->setPrenom('Yassine');
        $user->setPassword('123456789');

        $manager = new UserManager();
        $manager->validate($user);
    }

    public function testUserWithInvalidEmail()
    {
        $this->expectException(\InvalidArgumentException::class);

        $user = new User();
        $user->setUsername('Aouadi');
        $user->setNom('Aouadi');
        $user->setPrenom('yassine');
        $user->setEmail('MohamedYassine.aouadi.esprit.tn');
        $user->setPassword('123456789');

        $manager = new UserManager();
        $manager->validate($user);
    }

    public function testUserWithShortPassword()
    {
        $this->expectException(\InvalidArgumentException::class);

        $user = new User();
        $user->setUsername('Aouadi');
        $user->setNom('Aouadi');
        $user->setPrenom('Yassine');
        $user->setEmail('mohamedyassine.aouadi@esprit.tn');
        $user->setPassword('123');

        $manager = new UserManager();
        $manager->validate($user);
    }
}