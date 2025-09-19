<?php

namespace DevHelm\Control\Controller\App;

use DevHelm\Control\Dto\App\Request\CreateAgentDto;
use DevHelm\Control\Dto\App\Request\UpdateAgentDto;
use DevHelm\Control\Dto\Response\ErrorResponseDto;
use DevHelm\Control\Entity\Team;
use DevHelm\Control\Entity\User;
use DevHelm\Control\Factory\AgentFactory;
use DevHelm\Control\Repository\AgentRepositoryInterface;
use DevHelm\Control\Service\ApiKeyGenerator;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/app/agents')]
class AgentController
{
    use LoggerAwareTrait;

    #[Route('', name: 'app_agent_create', methods: ['POST'])]
    public function create(
        Request $request,
        AgentRepositoryInterface $agentRepository,
        AgentFactory $agentFactory,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        ApiKeyGenerator $apiKeyGenerator,
        LoggerInterface $logger,
        #[CurrentUser]
        User $user,
    ): JsonResponse {
        $this->setLogger($logger);
        $this->logger->info('Agent creation request received');
        try {
            $dto = $serializer->deserialize(
                $request->getContent(),
                CreateAgentDto::class,
                'json'
            );

            $violations = $validator->validate($dto);
            if (count($violations) > 0) {
                $errors = [];
                foreach ($violations as $violation) {
                    $errors[$violation->getPropertyPath()] = $violation->getMessage();
                }

                return new JsonResponse(['errors' => $errors], Response::HTTP_BAD_REQUEST);
            }

            $team = $user->getTeam();

            $agent = $agentFactory->createEntity($dto, $team);
            $agentRepository->save($agent);
            $apiKeyGenerator->generateForAgent($agent);

            $agentResponseDto = $agentFactory->createAgentResponseDto($agent);
            $responseData = $serializer->serialize($agentResponseDto, 'json');

            return new JsonResponse($responseData, Response::HTTP_CREATED, [], true);
        } catch (\Exception $e) {
            $this->logger->error('Error creating agent', [
                'exception_message' => $e->getMessage(),
            ]);

            $errorDto = new ErrorResponseDto('Internal server error', Response::HTTP_INTERNAL_SERVER_ERROR);

            return new JsonResponse($serializer->serialize($errorDto, 'json'), Response::HTTP_INTERNAL_SERVER_ERROR, [], true);
        }
    }

    #[Route('', name: 'app_agent_list', methods: ['GET'])]
    public function list(
        Request $request,
        AgentRepositoryInterface $agentRepository,
        AgentFactory $agentFactory,
        SerializerInterface $serializer,
        LoggerInterface $logger,
    ): JsonResponse {
        $this->setLogger($logger);
        $this->logger->info('Agent list request received');
        try {
            $user = $request->attributes->get('_user');
            $team = $user->getTeam();

            if (!$team instanceof Team) {
                return new JsonResponse(['error' => 'User must belong to a team'], Response::HTTP_FORBIDDEN);
            }

            $agents = $agentRepository->findByTeam($team);

            $agentDtos = array_map(function ($agent) use ($agentFactory) {
                return $agentFactory->createAgentResponseDto($agent);
            }, $agents);

            $responseDto = $agentFactory->createAgentListResponseDto(
                agentResponseDtos: $agentDtos,
                hasMore: false,
                lastKey: !empty($agentDtos) ? end($agentDtos)->id : null
            );

            return new JsonResponse($serializer->serialize($responseDto, 'json'), Response::HTTP_OK, [], true);
        } catch (\Exception $e) {
            $this->logger->error('Error retrieving agent list', [
                'exception_message' => $e->getMessage(),
            ]);

            $errorDto = new ErrorResponseDto('Internal server error', Response::HTTP_INTERNAL_SERVER_ERROR);

            return new JsonResponse($serializer->serialize($errorDto, 'json'), Response::HTTP_INTERNAL_SERVER_ERROR, [], true);
        }
    }

    #[Route('/app/agent/{id}/edit', name: 'app_agent_edit_get', methods: ['GET'])]
    public function getEditData(
        string $id,
        AgentRepositoryInterface $agentRepository,
        AgentFactory $agentFactory,
        SerializerInterface $serializer,
        #[CurrentUser]
        User $user,
    ): JsonResponse {
        $this->getLogger()->info('Agent edit data request received', ['agent_id' => $id]);
        try {
            $team = $user->getTeam();
            $agent = $agentRepository->getById($id);

            if (!$agent || $agent->getTeam() !== $team) {
                return new JsonResponse(['error' => 'Agent not found'], Response::HTTP_NOT_FOUND);
            }

            $agentResponseDto = $agentFactory->createAgentResponseDto($agent);
            $responseData = $serializer->serialize($agentResponseDto, 'json');

            return new JsonResponse($responseData, Response::HTTP_OK, [], true);
        } catch (\Exception $e) {
            $this->getLogger()->error('Error retrieving agent edit data', [
                'exception_message' => $e->getMessage(),
                'agent_id' => $id,
            ]);

            $errorDto = new ErrorResponseDto('Internal server error', Response::HTTP_INTERNAL_SERVER_ERROR);

            return new JsonResponse($serializer->serialize($errorDto, 'json'), Response::HTTP_INTERNAL_SERVER_ERROR, [], true);
        }
    }

    #[Route('/app/agent/{id}/edit', name: 'app_agent_edit_post', methods: ['POST'])]
    public function editAgent(
        string $id,
        Request $request,
        AgentRepositoryInterface $agentRepository,
        AgentFactory $agentFactory,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        #[CurrentUser]
        User $user,
    ): JsonResponse {
        $this->getLogger()->info('Agent edit request received', ['agent_id' => $id]);
        try {
            $team = $user->getTeam();
            $agent = $agentRepository->getById($id);

            if (!$agent || $agent->getTeam() !== $team) {
                return new JsonResponse(['error' => 'Agent not found'], Response::HTTP_NOT_FOUND);
            }

            $dto = $serializer->deserialize(
                $request->getContent(),
                UpdateAgentDto::class,
                'json'
            );

            $violations = $validator->validate($dto);
            if (count($violations) > 0) {
                $errors = [];
                foreach ($violations as $violation) {
                    $errors[$violation->getPropertyPath()] = $violation->getMessage();
                }

                return new JsonResponse(['errors' => $errors], Response::HTTP_BAD_REQUEST);
            }

            $agent->setName($dto->name);
            $agent->setProject($dto->project);
            $agent->setUpdatedAt(new \DateTimeImmutable());

            $agentRepository->save($agent);

            $agentResponseDto = $agentFactory->createAgentResponseDto($agent);
            $responseData = $serializer->serialize($agentResponseDto, 'json');

            return new JsonResponse($responseData, Response::HTTP_OK, [], true);
        } catch (\Exception $e) {
            $this->getLogger()->error('Error updating agent', [
                'exception_message' => $e->getMessage(),
                'agent_id' => $id,
            ]);

            $errorDto = new ErrorResponseDto('Internal server error', Response::HTTP_INTERNAL_SERVER_ERROR);

            return new JsonResponse($serializer->serialize($errorDto, 'json'), Response::HTTP_INTERNAL_SERVER_ERROR, [], true);
        }
    }
}
