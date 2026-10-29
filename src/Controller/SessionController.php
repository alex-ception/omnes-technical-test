<?php

declare(strict_types=1);

namespace App\Controller;

use App\Session\Application\Query\ListerSessions;
use App\Shared\Application\BusDeRequetes;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SessionController extends AbstractController
{
    #[Route('/', name: 'app_accueil', methods: ['GET'])]
    public function accueil(): Response
    {
        return $this->redirectToRoute('app_sessions');
    }

    #[Route('/sessions', name: 'app_sessions', methods: ['GET'])]
    public function index(BusDeRequetes $requetes): Response
    {
        return $this->render('session/index.html.twig', [
            'sessions' => $requetes->demander(new ListerSessions()),
        ]);
    }
}
