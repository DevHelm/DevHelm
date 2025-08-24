<?php

namespace App\Controller\App;

use App\Dto\App\Request\CreateAgentDto;
use App\Entity\Team;
use App\Entity\User;
use App\Factory\AgentFactory;
use App\Repository\AgentRepositoryInterface;
use App\Service\ApiKeyGenerator;
use Parthenon\User\Entity\UserInterface;
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

            $agent = $agentFactory->createFromDto($dto, $team);
            $agentRepository->save($agent);

            $agentResponseDto = $agentFactory->createAgentResponseDto($agent);
            $responseData = $serializer->serialize($agentResponseDto, 'json');

            return new JsonResponse($responseData, Response::HTTP_CREATED, [], true);
        } catch (\Exception $e) {
            $this->logger->error('Error creating agent: '.$e->getMessage(), [
                'exception' => $e,
            ]);

            return new JsonResponse(['error' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
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
            /** @var UserInterface $user */
            $user = $request->attributes->get('_user');
            $team = $user->getTeam();

            if (!$team instanceof Team) {
                return new JsonResponse(['error' => 'User must belong to a team'], Response::HTTP_FORBIDDEN);
            }

            $agents = $agentRepository->findByTeam($team);

            $data = array_map(function ($agent) use ($agentFactory) {
                return $agentFactory->createAgentResponseDto($agent);
            }, $agents);

            return new JsonResponse($serializer->serialize($data, 'json'), Response::HTTP_OK, [], true);
        } catch (\Exception $e) {
            $this->logger->error('Error retrieving agent list: '.$e->getMessage(), [
                'exception' => $e,
            ]);

            return new JsonResponse(['error' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
