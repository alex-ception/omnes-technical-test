<?php

declare(strict_types=1);

namespace App\Session\Presentation\Http;

use App\Session\Application\Query\ListerSessions;
use App\Shared\Application\BusDeRequetes;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Liste des sessions pour l'équipe pédagogique.
 */
#[Route('/sessions', name: 'app_sessions', methods: ['GET'])]
final class ListeSessionsAction extends AbstractController
{
    public function __construct(
        private readonly BusDeRequetes $requetes,
    ) {
    }

    public function __invoke(): Response
    {
        return $this->render('session/index.html.twig', [
            'sessions' => $this->requetes->demander(new ListerSessions()),
        ]);
    }
}
