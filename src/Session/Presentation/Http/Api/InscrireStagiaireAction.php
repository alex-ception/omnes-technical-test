<?php

declare(strict_types=1);

namespace App\Session\Presentation\Http\Api;

use App\Session\Application\InscriptionService;
use App\Session\Domain\Exception\InscriptionImpossibleException;
use App\Session\Domain\Repository\SessionRepository;
use App\Session\Domain\Repository\StagiaireRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Inscription d'un stagiaire depuis le portail stagiaire.
 */
#[Route('/api/sessions/{code}/inscriptions', name: 'api_session_inscrire', methods: ['POST'])]
final class InscrireStagiaireAction extends AbstractController
{
    public function __construct(
        private readonly SessionRepository $sessions,
        private readonly StagiaireRepository $stagiaires,
        private readonly InscriptionService $inscriptionService,
    ) {
    }

    public function __invoke(string $code, Request $request): JsonResponse
    {
        $session = $this->sessions->trouverParCode($code);
        if (null === $session) {
            return $this->json(['erreur' => sprintf('Session %s inconnue.', $code)], Response::HTTP_NOT_FOUND);
        }

        $email = $request->getPayload()->getString('email');
        if ('' === $email) {
            return $this->json(['erreur' => 'Le champ "email" est obligatoire.'], Response::HTTP_BAD_REQUEST);
        }

        $stagiaire = $this->stagiaires->trouverParEmail($email);
        if (null === $stagiaire) {
            return $this->json(['erreur' => sprintf('Stagiaire %s inconnu.', $email)], Response::HTTP_NOT_FOUND);
        }

        try {
            $inscription = $this->inscriptionService->inscrire($stagiaire, $session);
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
