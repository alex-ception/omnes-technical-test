<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Formation;
use App\Entity\Inscription;
use App\Entity\Session;
use App\Entity\Stagiaire;
use App\Entity\StatutInscription;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Jeu de données de démonstration. Les tests s'appuient sur ces données : ne pas les modifier.
 */
final class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $formations = [
            'SYMF-ADV' => new Formation('SYMF-ADV', 'Symfony avancé'),
            'PHP-MOD' => new Formation('PHP-MOD', 'PHP moderne'),
            'DOCK-DEV' => new Formation('DOCK-DEV', 'Docker pour développeurs'),
            'GIT-QUO' => new Formation('GIT-QUO', 'Git au quotidien'),
        ];
        foreach ($formations as $formation) {
            $manager->persist($formation);
        }

        $stagiaires = [];
        foreach ([
            ['Alice', 'Martin'],
            ['Bruno', 'Petit'],
            ['Chloé', 'Durand'],
            ['David', 'Bernard'],
            ['Emma', 'Robert'],
            ['Farid', 'Benali'],
            ['Gaëlle', 'Moreau'],
            ['Hugo', 'Lefèvre'],
        ] as [$prenom, $nom]) {
            $email = $this->emailDepuis($prenom, $nom);
            $stagiaires[$email] = new Stagiaire($prenom, $nom, $email);
            $manager->persist($stagiaires[$email]);
        }

        $sessions = [
            'SYMF-ADV-01' => new Session($formations['SYMF-ADV'], 'SYMF-ADV-01', new \DateTimeImmutable('2026-11-03'), new \DateTimeImmutable('2026-11-05'), 5),
            'PHP-MOD-01' => new Session($formations['PHP-MOD'], 'PHP-MOD-01', new \DateTimeImmutable('2026-11-17'), new \DateTimeImmutable('2026-11-18'), 3),
            'DOCK-DEV-01' => new Session($formations['DOCK-DEV'], 'DOCK-DEV-01', new \DateTimeImmutable('2026-12-01'), new \DateTimeImmutable('2026-12-02'), 2),
            'GIT-QUO-01' => new Session($formations['GIT-QUO'], 'GIT-QUO-01', new \DateTimeImmutable('2026-12-10'), new \DateTimeImmutable('2026-12-10'), 10),
            'PHP-MOD-02' => new Session($formations['PHP-MOD'], 'PHP-MOD-02', new \DateTimeImmutable('2027-01-12'), new \DateTimeImmutable('2027-01-13'), 2),
        ];
        foreach ($sessions as $session) {
            $manager->persist($session);
        }

        $inscriptions = [
            ['SYMF-ADV-01', 'alice.martin@exemple.fr', StatutInscription::CONFIRMEE, '2026-09-01 09:12'],
            ['SYMF-ADV-01', 'bruno.petit@exemple.fr', StatutInscription::CONFIRMEE, '2026-09-02 14:30'],
            ['SYMF-ADV-01', 'chloe.durand@exemple.fr', StatutInscription::ANNULEE, '2026-09-03 10:05'],
            ['SYMF-ADV-01', 'david.bernard@exemple.fr', StatutInscription::ANNULEE, '2026-09-04 16:45'],

            ['PHP-MOD-01', 'emma.robert@exemple.fr', StatutInscription::CONFIRMEE, '2026-09-08 11:00'],
            ['PHP-MOD-01', 'farid.benali@exemple.fr', StatutInscription::CONFIRMEE, '2026-09-08 11:20'],
            ['PHP-MOD-01', 'gaelle.moreau@exemple.fr', StatutInscription::CONFIRMEE, '2026-09-09 08:50'],

            ['DOCK-DEV-01', 'hugo.lefevre@exemple.fr', StatutInscription::CONFIRMEE, '2026-09-15 09:00'],
            ['DOCK-DEV-01', 'alice.martin@exemple.fr', StatutInscription::ANNULEE, '2026-09-15 09:30'],
            ['DOCK-DEV-01', 'bruno.petit@exemple.fr', StatutInscription::LISTE_ATTENTE, '2026-09-16 17:10'],

            ['PHP-MOD-02', 'chloe.durand@exemple.fr', StatutInscription::ANNULEE, '2026-09-20 10:00'],
            ['PHP-MOD-02', 'david.bernard@exemple.fr', StatutInscription::ANNULEE, '2026-09-21 10:00'],
        ];
        foreach ($inscriptions as [$codeSession, $email, $statut, $date]) {
            $manager->persist(new Inscription(
                $sessions[$codeSession],
                $stagiaires[$email],
                $statut,
                new \DateTimeImmutable($date),
            ));
        }

        $manager->flush();
    }

    private function emailDepuis(string $prenom, string $nom): string
    {
        $normaliser = static fn (string $s): string => strtolower((string) preg_replace(
            '/[^a-z]/i',
            '',
            (string) iconv('UTF-8', 'ASCII//TRANSLIT', $s),
        ));

        return $normaliser($prenom).'.'.$normaliser($nom).'@exemple.fr';
    }
}
