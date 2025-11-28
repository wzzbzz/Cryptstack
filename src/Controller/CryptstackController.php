<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CryptstackController extends AbstractController
{
    #[Route('/cryptstack', name: 'app_cryptstack')]
    public function index(): Response
    {
        return $this->render('cryptstack/index.html.twig', [
            'controller_name' => 'CryptstackController',
        ]);
    }
}
