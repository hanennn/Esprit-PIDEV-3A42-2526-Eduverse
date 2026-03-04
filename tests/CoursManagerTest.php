<?php

namespace App\Tests\Service;

use App\Entity\Cours;
use App\Service\CoursManager;
use PHPUnit\Framework\TestCase;

class CoursManagerTest extends TestCase
{
    public function testValidCours()
    {
        $cours = new Cours();
        $cours->setTitreCours('Symfony 6');
        $cours->setDescription('Introduction au framework Symfony');

        $manager = new CoursManager();

        $this->assertTrue($manager->validate($cours));
    }

    public function testCoursWithoutTitle()
    {
        $this->expectException(\InvalidArgumentException::class);

        $cours = new Cours();
        $cours->setDescription('Description sans titre');

        $manager = new CoursManager();
        $manager->validate($cours);
    }

    public function testCoursWithoutDescription()
    {
        $this->expectException(\InvalidArgumentException::class);

        $cours = new Cours();
        $cours->setTitreCours('Cours sans description');

        $manager = new CoursManager();
        $manager->validate($cours);
    }
}