<?php

namespace App\Controller\App;

use App\Dto\App\Request\CreateAgentDto;
use App\Factory\AgentFactory;
use App\Factory\CreateAgentDtoFactory;
use App\Repository\AgentRepositoryInterface;
use App\Entity\Team;
use Parthenon\User\Entity\UserInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/app/agents')]
class AgentController
{
    #[Route('', name: 'app_agent_create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(
        Request $request,
        AgentRepositoryInterface $agentRepository,
        AgentFactory $agentFactory,
        CreateAgentDtoFactory $dtoFactory,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): JsonResponse {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!$data) {
                return new JsonResponse(['error' => 'Invalid JSON data'], Response::HTTP_BAD_REQUEST);
            }

            // Create DTO from request data using factory
            $dto = $dtoFactory->createFromArray($data);

            // Validate DTO
            $violations = $validator->validate($dto);
            if (count($violations) > 0) {
                $errors = [];
                foreach ($violations as $violation) {
                    $errors[$violation->getPropertyPath()] = $violation->getMessage();
                }
                return new JsonResponse(['errors' => $errors], Response::HTTP_BAD_REQUEST);
            }

            // Get current user and their team
            /** @var UserInterface $user */
            $user = $request->getUser();
            $team = $user->getTeam();
            
            if (!$team instanceof Team) {
                return new JsonResponse(['error' => 'User must belong to a team'], Response::HTTP_FORBIDDEN);
            }

            // Check if agent with same name already exists
            $existingAgent = $agentRepository->findByName($dto->name);
            if ($existingAgent) {
                return new JsonResponse(['error' => 'Agent with this name already exists'], Response::HTTP_CONFLICT);
            }

            // Create and persist agent
            $agent = $agentFactory->createFromDto($dto, $team);
            $agentRepository->save($agent);

            // Use the serializer to transform the agent to JSON
            $responseData = $serializer->serialize([
                'id' => $agent->getId()->toString(),
                'name' => $agent->getName(),
                'project' => $agent->getProject(),
                'team_id' => $agent->getTeam()->getId()->toString(),
                'created_at' => $agent->getCreatedAt()->format('Y-m-d H:i:s')
            ], 'json');

            return new JsonResponse($responseData, Response::HTTP_CREATED, [], true);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('', name: 'app_agent_list', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function list(
        Request $request,
        AgentRepositoryInterface $agentRepository,
        SerializerInterface $serializer
    ): JsonResponse {
        try {
            /** @var UserInterface $user */
            $user = $request->getUser();
            $team = $user->getTeam();
            
            if (!$team instanceof Team) {
                return new JsonResponse(['error' => 'User must belong to a team'], Response::HTTP_FORBIDDEN);
            }

            $agents = $agentRepository->findByTeam($team);
            
            $data = array_map(function($agent) {
                return [
                    'id' => $agent->getId()->toString(),
                    'name' => $agent->getName(),
                    'project' => $agent->getProject(),
                    'created_at' => $agent->getCreatedAt()->format('Y-m-d H:i:s')
                ];
            }, $agents);

            return new JsonResponse($serializer->serialize($data, 'json'), Response::HTTP_OK, [], true);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}