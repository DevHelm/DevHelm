<?php

namespace App\Controller;

use App\Dto\CreateAgentDto;
use App\Factory\AgentFactory;
use App\Repository\AgentRepositoryInterface;
use App\Entity\Team;
use Doctrine\ORM\EntityManagerInterface;
use Parthenon\User\Entity\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/agents')]
class AgentController extends AbstractController
{
    public function __construct(
        private readonly AgentRepositoryInterface $agentRepository,
        private readonly AgentFactory $agentFactory,
        private readonly EntityManagerInterface $entityManager,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator
    ) {
    }

    #[Route('', name: 'api_agent_create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!$data) {
                return $this->json(['error' => 'Invalid JSON data'], Response::HTTP_BAD_REQUEST);
            }

            // Create DTO from request data
            $dto = new CreateAgentDto(
                $data['name'] ?? '',
                $data['project'] ?? ''
            );

            // Validate DTO
            $violations = $this->validator->validate($dto);
            if (count($violations) > 0) {
                $errors = [];
                foreach ($violations as $violation) {
                    $errors[$violation->getPropertyPath()] = $violation->getMessage();
                }
                return $this->json(['errors' => $errors], Response::HTTP_BAD_REQUEST);
            }

            // Get current user and their team
            /** @var UserInterface $user */
            $user = $this->getUser();
            $team = $user->getTeam();
            
            if (!$team instanceof Team) {
                return $this->json(['error' => 'User must belong to a team'], Response::HTTP_FORBIDDEN);
            }

            // Check if agent with same name already exists
            $existingAgent = $this->agentRepository->findByName($dto->getName());
            if ($existingAgent) {
                return $this->json(['error' => 'Agent with this name already exists'], Response::HTTP_CONFLICT);
            }

            // Create and persist agent
            $agent = $this->agentFactory->createFromDto($dto, $team);
            $this->entityManager->persist($agent);
            $this->entityManager->flush();

            return $this->json([
                'id' => $agent->getId()->toString(),
                'name' => $agent->getName(),
                'project' => $agent->getProject(),
                'team_id' => $agent->getTeam()->getId()->toString(),
                'created_at' => $agent->getCreatedAt()->format('Y-m-d H:i:s')
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return $this->json(['error' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('', name: 'api_agent_list', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function list(): JsonResponse
    {
        try {
            /** @var UserInterface $user */
            $user = $this->getUser();
            $team = $user->getTeam();
            
            if (!$team instanceof Team) {
                return $this->json(['error' => 'User must belong to a team'], Response::HTTP_FORBIDDEN);
            }

            $agents = $this->agentRepository->findByTeam($team);
            
            $data = array_map(function($agent) {
                return [
                    'id' => $agent->getId()->toString(),
                    'name' => $agent->getName(),
                    'project' => $agent->getProject(),
                    'created_at' => $agent->getCreatedAt()->format('Y-m-d H:i:s')
                ];
            }, $agents);

            return $this->json($data);

        } catch (\Exception $e) {
            return $this->json(['error' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}