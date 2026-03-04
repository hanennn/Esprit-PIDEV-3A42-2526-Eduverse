<?php

namespace App\Tests\Service;

use App\Entity\Chapitre;
use App\Entity\Chapitres;
use App\Entity\Cours;
use App\Service\ChapitreManager;
use PHPUnit\Framework\TestCase;

class ChapitreManagerTest extends TestCase
{
    public function testValidChapitre()
    {
        $cours = new Cours();
        $cours->setTitreCours('Symfony');
        $cours->setDescription('Cours Symfony');

        $chapitre = new Chapitres();
        $chapitre->setTitreChap('Introduction');
        $chapitre->setCours($cours);

        $manager = new ChapitreManager();

        $this->assertTrue($manager->validate($chapitre));
    }

    public function testChapitreWithoutTitle()
    {
        $this->expectException(\InvalidArgumentException::class);

        $cours = new Cours();

        $chapitre = new Chapitres();
        $chapitre->setCours($cours);

        $manager = new ChapitreManager();
        $manager->validate($chapitre);
    }

    public function testChapitreWithoutCours()
    {
        $this->expectException(\InvalidArgumentException::class);

        $chapitre = new Chapitres();
        $chapitre->setTitreChap('Chapitre sans cours');

        $manager = new ChapitreManager();
        $manager->validate($chapitre);
    }
}