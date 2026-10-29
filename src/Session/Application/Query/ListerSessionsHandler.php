<?php

declare(strict_types=1);

namespace App\Session\Application\Query;

use App\Session\Domain\Repository\SessionRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class ListerSessionsHandler
{
    public function __construct(
        private SessionRepository $sessions,
    ) {
    }

    /**
     * @return list<ResumeSession>
     */
    public function __invoke(ListerSessions $requete): array
    {
        return array_map(ResumeSession::depuisSession(...), $this->sessions->listerParDateDeDebut());
    }
}
