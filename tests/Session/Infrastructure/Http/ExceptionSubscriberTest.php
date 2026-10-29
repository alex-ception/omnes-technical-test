<?php

declare(strict_types=1);

namespace App\Tests\Session\Infrastructure\Http;

use App\Session\Domain\Exception\InscriptionImpossibleException;
use App\Session\Domain\Exception\SessionIntrouvableException;
use App\Session\Domain\Exception\StagiaireIntrouvableException;
use App\Session\Infrastructure\Http\ExceptionSubscriber;
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
        yield 'inscription impossible' => [new InscriptionImpossibleException('La session est pleine.'), 409, 'La session est pleine.'];
        yield 'session introuvable' => [new SessionIntrouvableException('INCONNUE-01'), 404, 'Session INCONNUE-01 inconnue.'];
        yield 'stagiaire introuvable' => [new StagiaireIntrouvableException('inconnu@exemple.fr'), 404, 'Stagiaire inconnu@exemple.fr inconnu.'];
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
        $evenement = $this->evenement('/sessions', new SessionIntrouvableException('INCONNUE-01'));

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
