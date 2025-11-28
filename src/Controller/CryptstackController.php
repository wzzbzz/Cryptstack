<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CryptstackController extends AbstractController
{
    #[Route('/', name: 'app_cryptstack')]
    public function index(): Response
    {
        // Path to your static UI file inside /public
        $filePath = $this->getParameter('kernel.project_dir') . '/public/nativex-ui.html';

        if (!file_exists($filePath)) {
            return new Response("UI file not found", 404);
        }

        return new Response(
            file_get_contents($filePath),
            200,
            ['Content-Type' => 'text/html']
        );
    }
}
