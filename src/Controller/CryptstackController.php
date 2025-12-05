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

    #[Route('/suite', name: 'app_cryptstack_suite')]
    public function suite(): Response
    {
        $filePath = $this->getParameter('kernel.project_dir') . '/public/suite-ui.html';

        if (!file_exists($filePath)) {
            return new Response("Suite UI file not found", 404);
        }

        return new Response(
            file_get_contents($filePath),
            200,
            ['Content-Type' => 'text/html']
        );
    }
}
