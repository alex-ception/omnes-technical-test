<?php

declare(strict_types=1);

namespace App\Session\Infrastructure\Http;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/', name: 'app_accueil', methods: ['GET'])]
final class AccueilAction extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->redirectToRoute('app_sessions');
    }
}
