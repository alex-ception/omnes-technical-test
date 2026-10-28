<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Exception\InscriptionImpossibleException;
use App\Repository\SessionRepository;
use App\Repository\StagiaireRepository;
use App\Service\InscriptionService;
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
    public function liste(SessionRepository $sessions): JsonResponse
    {
        $donnees = [];

        foreach ($sessions->findAllAvecFormation() as $session) {
            $donnees[] = [
                'code' => $session->getCode(),
                'formation' => $session->getFormation()->getTitre(),
                'date_debut' => $session->getDateDebut()->format('Y-m-d'),
                'date_fin' => $session->getDateFin()->format('Y-m-d'),
                'capacite' => $session->getCapacite(),
                'places_restantes' => $session->getPlacesRestantes(),
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
        $session = $sessions->findOneByCode($code);
        if (null === $session) {
            return $this->json(['erreur' => sprintf('Session %s inconnue.', $code)], Response::HTTP_NOT_FOUND);
        }

        $email = $request->getPayload()->getString('email');
        if ('' === $email) {
            return $this->json(['erreur' => 'Le champ "email" est obligatoire.'], Response::HTTP_BAD_REQUEST);
        }

        $stagiaire = $stagiaires->findOneByEmail($email);
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
