<?php

declare(strict_types=1);

namespace App\Session\Presentation\Http\Api;

use App\Session\Application\Query\ListerSessions;
use App\Session\Application\Query\ResumeSession;
use App\Shared\Application\BusDeRequetes;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Liste des sessions pour le portail stagiaire.
 */
#[Route('/api/sessions', name: 'api_sessions', methods: ['GET'])]
final class ListeSessionsAction extends AbstractController
{
    public function __construct(
        private readonly BusDeRequetes $requetes,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $donnees = [];

        /** @var ResumeSession $session */
        foreach ($this->requetes->demander(new ListerSessions()) as $session) {
            $donnees[] = [
                'code' => $session->code,
                'formation' => $session->formation,
                'date_debut' => $session->dateDebut->format('Y-m-d'),
                'date_fin' => $session->dateFin->format('Y-m-d'),
                'capacite' => $session->capacite,
                'places_restantes' => $session->placesRestantes,
            ];
        }

        return $this->json($donnees);
    }
}
