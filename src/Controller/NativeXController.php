<?php

namespace App\Controller;

use App\Service\NativeX\NativeX;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class NativeXController extends AbstractController
{
    #[Route('/encode', methods: ['POST'])]
    public function encode(Request $request, NativeX $nativex): JsonResponse
    {
        $body = json_decode($request->getContent(), true);

        $text = $body['text'] ?? '';
        $stack = $body['stack'] ?? null;
        $key = $body['key'] ?? null;

        if ($stack) {
            $nativex->stack = array_map('trim', explode(',', $stack));
        }


        if( $key ) {
            $nativex->setKey($key);
        }
        
    
        $encoded = $nativex->stack($text, 1);

        return new JsonResponse(['encoded' => $encoded]);
    }

    #[Route('/decode', methods: ['POST'])]
    public function decode(Request $request, NativeX $nativex): JsonResponse
    {
        $body = json_decode($request->getContent(), true);

        $text = $body['text'] ?? '';
        $stack = $body['stack'] ?? null;
        $key = $body['key'] ?? null;

        if ($stack) {
            $nativex->stack = array_map('trim', explode(',', $stack));
        }

        if( $key ) {
            $nativex->setKey($key);
        }

        $decoded = $nativex->stack($text, -1);

        return new JsonResponse(['decoded' => $decoded]);
    }
}
