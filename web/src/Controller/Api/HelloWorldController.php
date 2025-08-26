<?php

namespace App\Controller\Api;

use App\Security\AgentUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * Simple API controller for testing Agent authentication.
 */
#[Route('/api/v1')]
class HelloWorldController extends AbstractController
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * Simple hello world endpoint to verify API authentication.
     */
    #[Route('/hello-world', name: 'api_hello_world', methods: ['GET'])]
    public function helloWorld(): JsonResponse
    {
        $response = [
            'hello' => 'world'
        ];
        
        // Add agent info if available
        if ($this->security->getUser() instanceof AgentUser) {
            /** @var AgentUser $user */
            $user = $this->security->getUser();
            $agent = $user->getAgent();
            
            $response['agent'] = [
                'id' => $agent->getId(),
                'name' => $agent->getName(),
                'authenticated' => true
            ];
        }
        
        return new JsonResponse($response);
    }
}