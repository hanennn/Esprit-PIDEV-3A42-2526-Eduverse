<?php

namespace App\Tests\Service;

use App\Entity\Event;
use App\Service\EventService;
use PHPUnit\Framework\TestCase;

class EventServiceTest extends TestCase
{
    private EventService $service;

    protected function setUp(): void
    {
        $this->service = new EventService();
    }

    // =========================================================================
    //  Helper : crée un Event valide par défaut
    // =========================================================================

    private function creerEventValide(): Event
    {
        $event = new Event();
        $event->setTitre('Atelier Symfony Avancé');
        $event->setDescription('Un atelier complet sur les bonnes pratiques Symfony et Doctrine.');
        $event->setType('atelier');
        $event->setNiveau('intermediaire');
        $event->setDate(new \DateTime('+1 month'));
        $event->setHeureDeb(new \DateTime('09:00'));
        $event->setHeureFin(new \DateTime('12:00'));

        return $event;
    }

    // =========================================================================
    //  TESTS DE VALIDATION (contrôle de saisie)
    // =========================================================================

    public function testEventValideAucuneErreur(): void
    {
        $event = $this->creerEventValide();
        $erreurs = $this->service->validerEvent($event);

        $this->assertEmpty($erreurs, 'Un événement valide ne doit produire aucune erreur.');
    }

    // --- Titre ---

    public function testTitreVide(): void
    {
        $event = $this->creerEventValide();
        $event->setTitre('');

        $erreurs = $this->service->validerEvent($event);
        $this->assertContains("Le titre est obligatoire.", $erreurs);
    }

    public function testTitreTropCourt(): void
    {
        $event = $this->creerEventValide();
        $event->setTitre('AB');

        $erreurs = $this->service->validerEvent($event);
        $this->assertContains("Le titre doit contenir au moins 3 caractères.", $erreurs);
    }

    public function testTitreTropLong(): void
    {
        $event = $this->creerEventValide();
        $event->setTitre(str_repeat('X', 256));

        $erreurs = $this->service->validerEvent($event);
        $this->assertContains("Le titre ne doit pas dépasser 255 caractères.", $erreurs);
    }

    // --- Description ---

    public function testDescriptionVide(): void
    {
        $event = $this->creerEventValide();
        $event->setDescription('');

        $erreurs = $this->service->validerEvent($event);
        $this->assertContains("La description est obligatoire.", $erreurs);
    }

    public function testDescriptionTropCourte(): void
    {
        $event = $this->creerEventValide();
        $event->setDescription('Court');

        $erreurs = $this->service->validerEvent($event);
        $this->assertContains("La description doit contenir au moins 10 caractères.", $erreurs);
    }

    // --- Type ---

    public function testTypeVide(): void
    {
        $event = $this->creerEventValide();
        $event->setType('');

        $erreurs = $this->service->validerEvent($event);
        $this->assertContains("Le type est obligatoire.", $erreurs);
    }

    public function testTypeInvalide(): void
    {
        $event = $this->creerEventValide();
        $event->setType('conference');

        $erreurs = $this->service->validerEvent($event);
        $this->assertStringContainsString("n'est pas valide", $erreurs[0]);
    }

    /**
     * @dataProvider typesValidesProvider
     */
    public function testTypesValides(string $type, ?string $lienWebinaire): void
    {
        $event = $this->creerEventValide();
        $event->setType($type);
        if ($lienWebinaire !== null) {
            $event->setLienWebinaire($lienWebinaire);
        }

        $erreurs = $this->service->validerEvent($event);
        $this->assertEmpty($erreurs);
    }

    public static function typesValidesProvider(): array
    {
        return [
            'webinaire'  => ['webinaire', 'https://zoom.us/j/123456789'],
            'atelier'    => ['atelier', null],
            'challenge'  => ['challenge', null],
        ];
    }

    // --- Niveau ---

    public function testNiveauInvalide(): void
    {
        $event = $this->creerEventValide();
        $event->setNiveau('expert');

        $erreurs = $this->service->validerEvent($event);
        $this->assertStringContainsString("niveau", $erreurs[0]);
    }

    public function testNiveauNullEstAccepte(): void
    {
        $event = $this->creerEventValide();
        $event->setNiveau(null);

        $erreurs = $this->service->validerEvent($event);
        $this->assertEmpty($erreurs);
    }

    /**
     * @dataProvider niveauxValidesProvider
     */
    public function testNiveauxValides(string $niveau): void
    {
        $event = $this->creerEventValide();
        $event->setNiveau($niveau);

        $erreurs = $this->service->validerEvent($event);
        $this->assertEmpty($erreurs);
    }

    public static function niveauxValidesProvider(): array
    {
        return [
            'debutant'      => ['debutant'],
            'intermediaire' => ['intermediaire'],
            'avance'        => ['avance'],
        ];
    }

    // --- Lien webinaire ---

    public function testLienWebinaireObligatoirePourWebinaire(): void
    {
        $event = $this->creerEventValide();
        $event->setType('webinaire');
        $event->setLienWebinaire(null);

        $erreurs = $this->service->validerEvent($event);
        $this->assertContains(
            "Le lien du webinaire est obligatoire pour un événement de type webinaire.",
            $erreurs
        );
    }

    public function testLienWebinaireUrlInvalide(): void
    {
        $event = $this->creerEventValide();
        $event->setType('webinaire');
        $event->setLienWebinaire('pas-une-url');

        $erreurs = $this->service->validerEvent($event);
        $this->assertContains("Le lien du webinaire doit être une URL valide.", $erreurs);
    }

    public function testLienWebinaireValide(): void
    {
        $event = $this->creerEventValide();
        $event->setType('webinaire');
        $event->setLienWebinaire('https://zoom.us/j/123456789');

        $erreurs = $this->service->validerEvent($event);
        $this->assertEmpty($erreurs);
    }

    public function testLienWebinaireIgnorePourAtelier(): void
    {
        $event = $this->creerEventValide();
        $event->setType('atelier');
        $event->setLienWebinaire(null);

        $erreurs = $this->service->validerEvent($event);
        $this->assertEmpty($erreurs);
    }

    // --- Date ---

    public function testDateNull(): void
    {
        $event = $this->creerEventValide();
        $ref = new \ReflectionProperty(Event::class, 'date');
        $ref->setValue($event, null);

        $erreurs = $this->service->validerEvent($event);
        $this->assertContains("La date est obligatoire.", $erreurs);
    }

    // --- Heures ---

    public function testHeureDebNull(): void
    {
        $event = $this->creerEventValide();
        $ref = new \ReflectionProperty(Event::class, 'heureDeb');
        $ref->setValue($event, null);

        $erreurs = $this->service->validerEvent($event);
        $this->assertContains("L'heure de début est obligatoire.", $erreurs);
    }

    public function testHeureFinNull(): void
    {
        $event = $this->creerEventValide();
        $ref = new \ReflectionProperty(Event::class, 'heureFin');
        $ref->setValue($event, null);

        $erreurs = $this->service->validerEvent($event);
        $this->assertContains("L'heure de fin est obligatoire.", $erreurs);
    }

    public function testHeureFinAvantHeureDeb(): void
    {
        $event = $this->creerEventValide();
        $event->setHeureDeb(new \DateTime('14:00'));
        $event->setHeureFin(new \DateTime('10:00'));

        $erreurs = $this->service->validerEvent($event);
        $this->assertContains("L'heure de fin doit être postérieure à l'heure de début.", $erreurs);
    }

    public function testHeureFinEgaleHeureDeb(): void
    {
        $event = $this->creerEventValide();
        $event->setHeureDeb(new \DateTime('10:00'));
        $event->setHeureFin(new \DateTime('10:00'));

        $erreurs = $this->service->validerEvent($event);
        $this->assertContains("L'heure de fin doit être postérieure à l'heure de début.", $erreurs);
    }

    // --- Image ---

    public function testImageExtensionInvalide(): void
    {
        $event = $this->creerEventValide();
        $event->setImage('fichier.bmp');

        $erreurs = $this->service->validerEvent($event);
        $this->assertNotEmpty($erreurs);
        $this->assertStringContainsString('format', $erreurs[0]);
    }

    public function testImageValide(): void
    {
        $event = $this->creerEventValide();
        $event->setImage('event-banner.png');

        $erreurs = $this->service->validerEvent($event);
        $this->assertEmpty($erreurs);
    }

    public function testImageNullAcceptee(): void
    {
        $event = $this->creerEventValide();
        $event->setImage(null);

        $erreurs = $this->service->validerEvent($event);
        $this->assertEmpty($erreurs);
    }

    // =========================================================================
    //  TESTS DE FILTRAGE
    // =========================================================================

    /**
     * @return Event[]
     */
    private function creerJeuDEvents(): array
    {
        $e1 = new Event();
        $e1->setTitre('Webinaire IA Générative');
        $e1->setType('webinaire');
        $e1->setNiveau('avance');
        $e1->setDate(new \DateTime('+2 weeks'));

        $e2 = new Event();
        $e2->setTitre('Atelier React');
        $e2->setType('atelier');
        $e2->setNiveau('debutant');
        $e2->setDate(new \DateTime('+1 month'));

        $e3 = new Event();
        $e3->setTitre('Challenge Hackathon IA');
        $e3->setType('challenge');
        $e3->setNiveau('intermediaire');
        $e3->setDate(new \DateTime('-1 week'));

        $e4 = new Event();
        $e4->setTitre('Atelier Docker Avancé');
        $e4->setType('atelier');
        $e4->setNiveau('avance');
        $e4->setDate(new \DateTime('+3 months'));

        return [$e1, $e2, $e3, $e4];
    }

    public function testFiltreParTitre(): void
    {
        $events = $this->creerJeuDEvents();

        $resultat = $this->service->filtrerEvents($events, ['titre' => 'IA']);

        $this->assertCount(2, $resultat);
        $this->assertSame('Webinaire IA Générative', $resultat[0]->getTitre());
        $this->assertSame('Challenge Hackathon IA', $resultat[1]->getTitre());
    }

    public function testFiltreParTitreCasseInsensible(): void
    {
        $events = $this->creerJeuDEvents();

        $resultat = $this->service->filtrerEvents($events, ['titre' => 'atelier']);

        $this->assertCount(2, $resultat);
    }

    public function testFiltreParType(): void
    {
        $events = $this->creerJeuDEvents();

        $resultat = $this->service->filtrerEvents($events, ['type' => 'atelier']);

        $this->assertCount(2, $resultat);
        $this->assertSame('atelier', $resultat[0]->getType());
        $this->assertSame('atelier', $resultat[1]->getType());
    }

    public function testFiltreParNiveau(): void
    {
        $events = $this->creerJeuDEvents();

        $resultat = $this->service->filtrerEvents($events, ['niveau' => 'avance']);

        $this->assertCount(2, $resultat);
    }

    public function testFiltreAVenir(): void
    {
        $events = $this->creerJeuDEvents();

        $resultat = $this->service->filtrerEvents($events, ['aVenir' => true]);

        $this->assertCount(3, $resultat);
        foreach ($resultat as $event) {
            $this->assertGreaterThanOrEqual(
                new \DateTime('today'),
                $event->getDate()
            );
        }
    }

    public function testFiltreParPlageDeDates(): void
    {
        $events = $this->creerJeuDEvents();

        $resultat = $this->service->filtrerEvents($events, [
            'dateMin' => new \DateTime('today'),
            'dateMax' => new \DateTime('+2 months'),
        ]);

        $this->assertCount(2, $resultat);
    }

    public function testFiltreCombineTypeEtNiveau(): void
    {
        $events = $this->creerJeuDEvents();

        $resultat = $this->service->filtrerEvents($events, [
            'type'   => 'atelier',
            'niveau' => 'avance',
        ]);

        $this->assertCount(1, $resultat);
        $this->assertSame('Atelier Docker Avancé', $resultat[0]->getTitre());
    }

    public function testFiltreCombineTitreTypeAVenir(): void
    {
        $events = $this->creerJeuDEvents();

        $resultat = $this->service->filtrerEvents($events, [
            'titre'  => 'IA',
            'aVenir' => true,
        ]);

        $this->assertCount(1, $resultat);
        $this->assertSame('Webinaire IA Générative', $resultat[0]->getTitre());
    }

    public function testFiltreSansCritereRetourneTout(): void
    {
        $events = $this->creerJeuDEvents();

        $resultat = $this->service->filtrerEvents($events, []);

        $this->assertCount(4, $resultat);
    }

    public function testFiltreAucunResultat(): void
    {
        $events = $this->creerJeuDEvents();

        $resultat = $this->service->filtrerEvents($events, ['titre' => 'Inexistant']);

        $this->assertEmpty($resultat);
    }
}