<?php

declare(strict_types=1);

namespace App\Tests\Session\Infrastructure\Doctrine\Repository;

use App\Session\Domain\Model\Stagiaire;
use App\Session\Infrastructure\Doctrine\Mapper\StagiaireMapper;
use App\Session\Infrastructure\Doctrine\Repository\DoctrineStagiaireRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DoctrineStagiaireRepositoryTest extends KernelTestCase
{
    public function testTrouverParEmailRenvoieLeStagiaireDuDomaine(): void
    {
        self::bootKernel();

        $stagiaire = $this->stagiaires()->trouverParEmail('chloe.durand@exemple.fr');

        self::assertInstanceOf(Stagiaire::class, $stagiaire);
        self::assertNotNull($stagiaire->getId());
        self::assertSame('Chloé Durand', $stagiaire->getNomComplet());
    }

    public function testTrouverParEmailRenvoieNullPourUnStagiaireInconnu(): void
    {
        self::bootKernel();

        self::assertNull($this->stagiaires()->trouverParEmail('inconnu@exemple.fr'));
    }

    private function stagiaires(): DoctrineStagiaireRepository
    {
        return new DoctrineStagiaireRepository(
            static::getContainer()->get('doctrine.orm.entity_manager'),
            new StagiaireMapper(),
        );
    }
}
