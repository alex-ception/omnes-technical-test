<?php

declare(strict_types=1);

namespace App\Session\Infrastructure\Http;

use App\Session\Domain\Exception\InscriptionImpossibleException;
use App\Session\Domain\Exception\SessionIntrouvableException;
use App\Session\Domain\Exception\StagiaireIntrouvableException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Traduit les exceptions levées par les actions de l'API en réponses JSON {"erreur": "..."}.
 *
 * Les pages HTML et les exceptions inattendues sont laissées au traitement par défaut de Symfony.
 */
final class ExceptionSubscriber implements EventSubscriberInterface
{
    /** Code HTTP de chaque exception métier. */
    private const array CODES = [
        InscriptionImpossibleException::class => Response::HTTP_CONFLICT,
        SessionIntrouvableException::class => Response::HTTP_NOT_FOUND,
        StagiaireIntrouvableException::class => Response::HTTP_NOT_FOUND,
    ];

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => 'onKernelException'];
    }

    public function onKernelException(ExceptionEvent $evenement): void
    {
        if (!str_starts_with($evenement->getRequest()->getPathInfo(), '/api/')) {
            return;
        }

        $exception = $evenement->getThrowable();
        $code = $exception instanceof HttpExceptionInterface
            ? $exception->getStatusCode()
            : self::CODES[$exception::class] ?? null;

        if (null === $code) {
            return;
        }

        $evenement->setResponse(new JsonResponse(['erreur' => $exception->getMessage()], $code));
    }
}
