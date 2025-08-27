<?php

namespace DevHelm\Control\Controller\Api;

use App\Dto\Api\Response\HelloWorldResponseDto;
use App\Security\AgentUser;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/v1')]
class HelloWorldController extends AbstractController
{
    #[Route('/hello-world', name: 'api_hello_world', methods: ['GET'])]
    public function helloWorld(
        Security $security,
        SerializerInterface $serializer,
        LoggerInterface $logger
    ): JsonResponse {
        $logger->info('Received request to hello-world endpoint');

        $hello = 'world';
        $agent = null;

        if ($security->getUser() instanceof AgentUser) {
            $user = $security->getUser();
            $agentEntity = $user->getAgent();

            $hello = $agentEntity->getName();
            $agent = [
                'id' => $agentEntity->getId(),
                'name' => $agentEntity->getName(),
                'authenticated' => true,
            ];
            
            $logger->info('Authenticated user accessing hello-world endpoint', [
                'agent_id' => $agentEntity->getId(),
                'agent_name' => $agentEntity->getName()
            ]);
        } else {
            $logger->info('Unauthenticated access to hello-world endpoint');
        }

        $responseDto = new HelloWorldResponseDto($hello, $agent);
        $json = $serializer->serialize($responseDto, 'json');

        return new JsonResponse($json, json: true);
    }
}
