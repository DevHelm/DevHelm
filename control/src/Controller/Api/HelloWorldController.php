<?php

namespace DevHelm\Control\Controller\Api;

use DevHelm\Control\Security\AgentUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1')]
class HelloWorldController extends AbstractController
{
    #[Route('/hello-world', name: 'api_hello_world', methods: ['GET'])]
    public function helloWorld(Security $security): JsonResponse
    {
        $response = [];

        if ($this->security->getUser() instanceof AgentUser) {
            $user = $this->security->getUser();
            $agent = $user->getAgent();

            $response['hello'] = $agent->getName();
            $response['agent'] = [
                'id' => $agent->getId(),
                'name' => $agent->getName(),
                'authenticated' => true,
            ];
        } else {
            $response['hello'] = 'world';
        }

        return new JsonResponse($response);
    }
}
