<?php

declare(strict_types=1);

namespace App\Session\Presentation\Controller\Api;

use App\Session\Application\InscriptionService;
use App\Session\Application\Query\ListerSessions;
use App\Session\Application\Query\ResumeSession;
use App\Session\Domain\Exception\InscriptionImpossibleException;
use App\Session\Domain\Repository\SessionRepository;
use App\Session\Domain\Repository\StagiaireRepository;
use App\Shared\Application\BusDeRequetes;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * API consommée par le portail stagiaire.
 */
#[Route('/api/sessions')]
final class SessionApiController extends AbstractController
{
    #[Route('', name: 'api_sessions', methods: ['GET'])]
    public function liste(BusDeRequetes $requetes): JsonResponse
    {
        $donnees = [];

        /** @var ResumeSession $session */
        foreach ($requetes->demander(new ListerSessions()) as $session) {
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

    #[Route('/{code}/inscriptions', name: 'api_session_inscrire', methods: ['POST'])]
    public function inscrire(
        string $code,
        Request $request,
        SessionRepository $sessions,
        StagiaireRepository $stagiaires,
        InscriptionService $inscriptionService,
    ): JsonResponse {
        $session = $sessions->trouverParCode($code);
        if (null === $session) {
            return $this->json(['erreur' => sprintf('Session %s inconnue.', $code)], Response::HTTP_NOT_FOUND);
        }

        $email = $request->getPayload()->getString('email');
        if ('' === $email) {
            return $this->json(['erreur' => 'Le champ "email" est obligatoire.'], Response::HTTP_BAD_REQUEST);
        }

        $stagiaire = $stagiaires->trouverParEmail($email);
        if (null === $stagiaire) {
            return $this->json(['erreur' => sprintf('Stagiaire %s inconnu.', $email)], Response::HTTP_NOT_FOUND);
        }

        try {
            $inscription = $inscriptionService->inscrire($stagiaire, $session);
        } catch (InscriptionImpossibleException $e) {
            return $this->json(['erreur' => $e->getMessage()], Response::HTTP_CONFLICT);
        }

        return $this->json([
            'id' => $inscription->getId(),
            'session' => $session->getCode(),
            'stagiaire' => $stagiaire->getEmail(),
            'statut' => $inscription->getStatut()->value,
        ], Response::HTTP_CREATED);
    }
}
