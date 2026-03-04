<?php

namespace App\Tests\Service;

use App\Entity\Sujet;
use App\Entity\User;
use App\Service\SujetManager;
use PHPUnit\Framework\TestCase;

class SujetManagerTest extends TestCase
{
    public function testValidSujet(): void
    {
        $user = new User();
        $sujet = new Sujet();
        $sujet->setTitre('Titre valide');
        $sujet->setContenu('Contenu valide avec plus de dix caractères.');
        $sujet->setAuteur($user);

        $manager = new SujetManager();
        $this->assertTrue($manager->validate($sujet));
    }

    public function testSujetWithoutTitre(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $user = new User();
        $sujet = new Sujet();
        $sujet->setTitre(''); // vide
        $sujet->setContenu('Contenu valide avec plus de dix caractères.');
        $sujet->setAuteur($user);

        $manager = new SujetManager();
        $manager->validate($sujet);
    }

    public function testSujetWithShortTitre(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $user = new User();
        $sujet = new Sujet();
        $sujet->setTitre('abcd'); // 4 < 5
        $sujet->setContenu('Contenu valide avec plus de dix caractères.');
        $sujet->setAuteur($user);

        $manager = new SujetManager();
        $manager->validate($sujet);
    }

    public function testSujetWithShortContenu(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $user = new User();
        $sujet = new Sujet();
        $sujet->setTitre('Titre valide');
        $sujet->setContenu('court'); // < 10
        $sujet->setAuteur($user);

        $manager = new SujetManager();
        $manager->validate($sujet);
    }

    public function testSujetWithoutAuteur(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $sujet = new Sujet();
        $sujet->setTitre('Titre valide');
        $sujet->setContenu('Contenu valide avec plus de dix caractères.');
        // pas d'auteur

        $manager = new SujetManager();
        $manager->validate($sujet);
    }
}