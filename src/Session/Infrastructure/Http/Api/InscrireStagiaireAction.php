<?php

declare(strict_types=1);

namespace App\Session\Infrastructure\Http\Api;

use App\Session\Application\InscriptionService;
use App\Session\Domain\Exception\SessionIntrouvableException;
use App\Session\Domain\Exception\StagiaireIntrouvableException;
use App\Session\Domain\Repository\SessionRepository;
use App\Session\Domain\Repository\StagiaireRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Inscription d'un stagiaire depuis le portail stagiaire.
 *
 * Les erreurs (404, 400, 409) sont traduites en JSON par ExceptionSubscriber.
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
        $session = $this->sessions->trouverParCode($code) ?? throw new SessionIntrouvableException($code);

        $email = $request->getPayload()->getString('email');
        if ('' === $email) {
            throw new BadRequestHttpException('Le champ "email" est obligatoire.');
        }

        $stagiaire = $this->stagiaires->trouverParEmail($email) ?? throw new StagiaireIntrouvableException($email);

        $inscription = $this->inscriptionService->inscrire($stagiaire, $session);

        return $this->json([
            'id' => $inscription->getId(),
            'session' => $session->getCode(),
            'stagiaire' => $stagiaire->getEmail(),
            'statut' => $inscription->getStatut()->value,
        ], Response::HTTP_CREATED);
    }
}
