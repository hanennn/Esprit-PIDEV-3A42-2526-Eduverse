<?php

namespace App\Tests\Service;

use App\Entity\Bourse;
use App\Service\BourseService;
use PHPUnit\Framework\TestCase;

class BourseServiceTest extends TestCase
{
    private BourseService $service;

    protected function setUp(): void
    {
        $this->service = new BourseService();
    }

    // =========================================================================
    //  Helper : crée une Bourse valide par défaut
    // =========================================================================

    private function creerBourseValide(): Bourse
    {
        $bourse = new Bourse();
        $bourse->setTitre('Bourse Excellence 2026');
        $bourse->setDescription('Une bourse destinée aux étudiants méritants en informatique.');
        $bourse->setMontant(5000.0);
        $bourse->setDateAttribution(new \DateTime('+1 month'));
        $bourse->setDateFin(new \DateTime('+6 months'));

        return $bourse;
    }

    // =========================================================================
    //  TESTS DE VALIDATION (contrôle de saisie)
    // =========================================================================

    public function testBourseValideAucuneErreur(): void
    {
        $bourse = $this->creerBourseValide();
        $erreurs = $this->service->validerBourse($bourse);

        $this->assertEmpty($erreurs, 'Une bourse valide ne doit produire aucune erreur.');
    }

    // --- Titre ---

    public function testTitreVide(): void
    {
        $bourse = $this->creerBourseValide();
        $bourse->setTitre('');

        $erreurs = $this->service->validerBourse($bourse);
        $this->assertContains("Le titre est obligatoire.", $erreurs);
    }

    public function testTitreTropCourt(): void
    {
        $bourse = $this->creerBourseValide();
        $bourse->setTitre('AB');

        $erreurs = $this->service->validerBourse($bourse);
        $this->assertContains("Le titre doit contenir au moins 3 caractères.", $erreurs);
    }

    public function testTitreTropLong(): void
    {
        $bourse = $this->creerBourseValide();
        $bourse->setTitre(str_repeat('A', 256));

        $erreurs = $this->service->validerBourse($bourse);
        $this->assertContains("Le titre ne doit pas dépasser 255 caractères.", $erreurs);
    }

    // --- Description ---

    public function testDescriptionVide(): void
    {
        $bourse = $this->creerBourseValide();
        $bourse->setDescription('');

        $erreurs = $this->service->validerBourse($bourse);
        $this->assertContains("La description est obligatoire.", $erreurs);
    }

    public function testDescriptionTropCourte(): void
    {
        $bourse = $this->creerBourseValide();
        $bourse->setDescription('Court');

        $erreurs = $this->service->validerBourse($bourse);
        $this->assertContains("La description doit contenir au moins 10 caractères.", $erreurs);
    }

    // --- Montant ---

    public function testMontantNull(): void
    {
        $bourse = $this->creerBourseValide();
        // On utilise la réflexion pour forcer null sur un float
        $ref = new \ReflectionProperty(Bourse::class, 'montant');
        $ref->setValue($bourse, null);

        $erreurs = $this->service->validerBourse($bourse);
        $this->assertContains("Le montant est obligatoire.", $erreurs);
    }

    public function testMontantNegatif(): void
    {
        $bourse = $this->creerBourseValide();
        $bourse->setMontant(-100);

        $erreurs = $this->service->validerBourse($bourse);
        $this->assertContains("Le montant doit être supérieur à 0.", $erreurs);
    }

    public function testMontantZero(): void
    {
        $bourse = $this->creerBourseValide();
        $bourse->setMontant(0);

        $erreurs = $this->service->validerBourse($bourse);
        $this->assertContains("Le montant doit être supérieur à 0.", $erreurs);
    }

    public function testMontantTropEleve(): void
    {
        $bourse = $this->creerBourseValide();
        $bourse->setMontant(2_000_000);

        $erreurs = $this->service->validerBourse($bourse);
        $this->assertContains("Le montant ne peut pas dépasser 1 000 000.", $erreurs);
    }

    // --- Date d'attribution ---

    public function testDateAttributionNull(): void
    {
        $bourse = $this->creerBourseValide();
        $ref = new \ReflectionProperty(Bourse::class, 'dateAttribution');
        $ref->setValue($bourse, null);

        $erreurs = $this->service->validerBourse($bourse);
        $this->assertContains("La date d'attribution est obligatoire.", $erreurs);
    }

    public function testDateAttributionPassee(): void
    {
        $bourse = $this->creerBourseValide();
        $bourse->setDateAttribution(new \DateTime('-1 day'));

        $erreurs = $this->service->validerBourse($bourse);
        $this->assertContains("La date d'attribution doit être dans le futur.", $erreurs);
    }

    // --- Date de fin ---

    public function testDateFinNull(): void
    {
        $bourse = $this->creerBourseValide();
        $ref = new \ReflectionProperty(Bourse::class, 'dateFin');
        $ref->setValue($bourse, null);

        $erreurs = $this->service->validerBourse($bourse);
        $this->assertContains("La date de fin est obligatoire.", $erreurs);
    }

    public function testDateFinAvantDateAttribution(): void
    {
        $bourse = $this->creerBourseValide();
        $bourse->setDateAttribution(new \DateTime('+3 months'));
        $bourse->setDateFin(new \DateTime('+1 month'));

        $erreurs = $this->service->validerBourse($bourse);
        $this->assertContains("La date de fin doit être postérieure à la date d'attribution.", $erreurs);
    }

    // --- Image ---

    public function testImageExtensionInvalide(): void
    {
        $bourse = $this->creerBourseValide();
        $bourse->setImage('document.pdf');

        $erreurs = $this->service->validerBourse($bourse);
        $this->assertNotEmpty($erreurs);
        $this->assertStringContainsString('format', $erreurs[0]);
    }

    public function testImageExtensionValide(): void
    {
        $bourse = $this->creerBourseValide();
        $bourse->setImage('photo.jpg');

        $erreurs = $this->service->validerBourse($bourse);
        $this->assertEmpty($erreurs);
    }

    public function testImageNullEstAcceptee(): void
    {
        $bourse = $this->creerBourseValide();
        $bourse->setImage(null);

        $erreurs = $this->service->validerBourse($bourse);
        $this->assertEmpty($erreurs);
    }

    // =========================================================================
    //  TESTS DE FILTRAGE
    // =========================================================================

    /**
     * @return Bourse[]
     */
    private function creerJeuDeBourses(): array
    {
        $b1 = new Bourse();
        $b1->setTitre('Bourse Informatique');
        $b1->setMontant(3000);
        $b1->setDateAttribution(new \DateTime('+1 month'));
        $b1->setDateFin(new \DateTime('+4 months'));

        $b2 = new Bourse();
        $b2->setTitre('Bourse Médecine');
        $b2->setMontant(8000);
        $b2->setDateAttribution(new \DateTime('+2 months'));
        $b2->setDateFin(new \DateTime('+8 months'));

        $b3 = new Bourse();
        $b3->setTitre('Bourse Arts et Informatique');
        $b3->setMontant(1500);
        $b3->setDateAttribution(new \DateTime('-1 month'));  // déjà commencée
        $b3->setDateFin(new \DateTime('+2 months'));

        return [$b1, $b2, $b3];
    }

    public function testFiltreParTitre(): void
    {
        $bourses = $this->creerJeuDeBourses();

        $resultat = $this->service->filtrerBourses($bourses, ['titre' => 'Informatique']);

        $this->assertCount(2, $resultat);
        $this->assertSame('Bourse Informatique', $resultat[0]->getTitre());
        $this->assertSame('Bourse Arts et Informatique', $resultat[1]->getTitre());
    }

    public function testFiltreParTitreCasseInsensible(): void
    {
        $bourses = $this->creerJeuDeBourses();

        $resultat = $this->service->filtrerBourses($bourses, ['titre' => 'médecine']);

        $this->assertCount(1, $resultat);
        $this->assertSame('Bourse Médecine', $resultat[0]->getTitre());
    }

    public function testFiltreParMontantMin(): void
    {
        $bourses = $this->creerJeuDeBourses();

        $resultat = $this->service->filtrerBourses($bourses, ['montantMin' => 2500]);

        $this->assertCount(2, $resultat); // 3000 et 8000
    }

    public function testFiltreParMontantMax(): void
    {
        $bourses = $this->creerJeuDeBourses();

        $resultat = $this->service->filtrerBourses($bourses, ['montantMax' => 3000]);

        $this->assertCount(2, $resultat); // 3000 et 1500
    }

    public function testFiltreParPlagesDeMontant(): void
    {
        $bourses = $this->creerJeuDeBourses();

        $resultat = $this->service->filtrerBourses($bourses, [
            'montantMin' => 2000,
            'montantMax' => 5000,
        ]);

        $this->assertCount(1, $resultat); // seulement 3000
        $this->assertSame(3000.0, $resultat[0]->getMontant());
    }

    public function testFiltreEnCours(): void
    {
        $bourses = $this->creerJeuDeBourses();

        $resultat = $this->service->filtrerBourses($bourses, ['enCours' => true]);

        // Seule b3 a dateAttribution dans le passé et dateFin dans le futur
        $this->assertCount(1, $resultat);
        $this->assertSame('Bourse Arts et Informatique', $resultat[0]->getTitre());
    }

    public function testFiltreSansCritereRetourneTout(): void
    {
        $bourses = $this->creerJeuDeBourses();

        $resultat = $this->service->filtrerBourses($bourses, []);

        $this->assertCount(3, $resultat);
    }

    public function testFiltreCombineTitreEtMontant(): void
    {
        $bourses = $this->creerJeuDeBourses();

        $resultat = $this->service->filtrerBourses($bourses, [
            'titre'      => 'Informatique',
            'montantMin' => 2000,
        ]);

        $this->assertCount(1, $resultat);
        $this->assertSame('Bourse Informatique', $resultat[0]->getTitre());
    }

    public function testFiltreAucunResultat(): void
    {
        $bourses = $this->creerJeuDeBourses();

        $resultat = $this->service->filtrerBourses($bourses, ['titre' => 'Inexistant']);

        $this->assertEmpty($resultat);
    }
}