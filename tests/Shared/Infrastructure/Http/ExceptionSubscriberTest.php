<?php

declare(strict_types=1);

namespace App\Tests\Shared\Infrastructure\Http;

use App\Shared\Domain\Exception\RegleMetierEnfreinteException;
use App\Shared\Domain\Exception\RessourceIntrouvableException;
use App\Shared\Infrastructure\Http\ExceptionSubscriber;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

final class ExceptionSubscriberTest extends TestCase
{
    public function testLeSubscriberEcouteLesExceptionsDuKernel(): void
    {
        self::assertArrayHasKey(KernelEvents::EXCEPTION, ExceptionSubscriber::getSubscribedEvents());
    }

    /**
     * @return iterable<string, array{\Throwable, int, string}>
     */
    public static function exceptionsConnues(): iterable
    {
        yield 'règle métier enfreinte' => [
            new class('La session est pleine.') extends \DomainException implements RegleMetierEnfreinteException {},
            409,
            'La session est pleine.',
        ];
        yield 'ressource introuvable' => [
            new class('Session INCONNUE-01 inconnue.') extends \DomainException implements RessourceIntrouvableException {},
            404,
            'Session INCONNUE-01 inconnue.',
        ];
        yield 'exception HTTP' => [new BadRequestHttpException('Le champ "email" est obligatoire.'), 400, 'Le champ "email" est obligatoire.'];
    }

    #[DataProvider('exceptionsConnues')]
    public function testUneExceptionConnueDeLApiEstTraduiteEnReponseJson(\Throwable $exception, int $code, string $message): void
    {
        $evenement = $this->evenement('/api/sessions/GIT-QUO-01/inscriptions', $exception);

        (new ExceptionSubscriber())->onKernelException($evenement);

        $reponse = $evenement->getResponse();
        self::assertNotNull($reponse);
        self::assertSame($code, $reponse->getStatusCode());
        self::assertSame('application/json', $reponse->headers->get('Content-Type'));
        self::assertSame(['erreur' => $message], json_decode((string) $reponse->getContent(), true, 512, \JSON_THROW_ON_ERROR));
    }

    public function testUneExceptionInattendueEstLaisseeASymfony(): void
    {
        $evenement = $this->evenement('/api/sessions', new \RuntimeException('Panne.'));

        (new ExceptionSubscriber())->onKernelException($evenement);

        self::assertNull($evenement->getResponse());
    }

    public function testLesPagesHorsApiSontLaisseesASymfony(): void
    {
        $evenement = $this->evenement(
            '/sessions',
            new class('Session INCONNUE-01 inconnue.') extends \DomainException implements RessourceIntrouvableException {},
        );

        (new ExceptionSubscriber())->onKernelException($evenement);

        self::assertNull($evenement->getResponse());
    }

    private function evenement(string $chemin, \Throwable $exception): ExceptionEvent
    {
        return new ExceptionEvent(
            self::createStub(HttpKernelInterface::class),
            Request::create($chemin),
            HttpKernelInterface::MAIN_REQUEST,
            $exception,
        );
    }
}
