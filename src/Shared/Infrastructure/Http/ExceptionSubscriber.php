<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Http;

use App\Shared\Domain\Exception\RegleMetierEnfreinteException;
use App\Shared\Domain\Exception\RessourceIntrouvableException;
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
        $code = match (true) {
            $exception instanceof HttpExceptionInterface => $exception->getStatusCode(),
            $exception instanceof RessourceIntrouvableException => Response::HTTP_NOT_FOUND,
            $exception instanceof RegleMetierEnfreinteException => Response::HTTP_CONFLICT,
            default => null,
        };

        if (null === $code) {
            return;
        }

        $evenement->setResponse(new JsonResponse(['erreur' => $exception->getMessage()], $code));
    }
}
