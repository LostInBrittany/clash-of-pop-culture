<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\VoteDto;
use App\Service\GameEngine;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class GameController extends AbstractController
{
    #[Route('/api/state', methods: ['GET'])]
    public function state(GameEngine $engine): JsonResponse
    {
        return $this->json($engine->getState());
    }

    #[Route('/api/vote', methods: ['POST'])]
    public function vote(
        #[MapRequestPayload] VoteDto $vote,
        GameEngine $engine,
    ): JsonResponse {
        $engine->vote($vote->choice);
        
        return $this->json([
            'status' => 'received', 
            'choice' => $vote->choice
        ]);
    }
}