<?php

namespace DevHelm\Control\Controller\App;

use DevHelm\Control\Dto\App\Request\CreateLeadDto;
use DevHelm\Control\Entity\User;
use DevHelm\Control\Factory\LeadFactory;
use DevHelm\Control\Repository\Orm\InviteCodeRepository;
use DevHelm\Control\Repository\Orm\LeadRepository;
use DevHelm\Control\Repository\Orm\UserRepository;
use DevHelm\Control\User\Entity\EntityFactory;
use Parthenon\Common\LoggerAwareTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LeadController
{
    use LoggerAwareTrait;

    #[Route('/app/leads', name: 'app_lead_create', methods: ['POST'])]
    public function create(
        Request $request,
        LeadRepository $leadRepository,
        LeadFactory $leadFactory,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        UserRepository $userRepository,
        InviteCodeRepository $inviteCodeRepository,
        EntityFactory $entityFactory,
        #[CurrentUser]
        User $user,
    ): JsonResponse {
        $this->getLogger()->info('Lead creation request received');
        try {
            $dto = $serializer->deserialize(
                $request->getContent(),
                CreateLeadDto::class,
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

            // Check if a user already exists with this email
            $existingUser = $userRepository->findOneBy(['email' => $dto->email]);
            if ($existingUser) {
                return new JsonResponse([
                    'errors' => ['email' => 'A user with this email already exists'],
                ], Response::HTTP_BAD_REQUEST);
            }

            // Create the lead entity
            $lead = $leadFactory->createEntity($dto);
            $leadRepository->save($lead);

            // Create an invite code for the lead
            $inviteCode = $entityFactory->buildInviteCode($user, $dto->email);
            $inviteCodeRepository->save($inviteCode);

            $leadResponseDto = $leadFactory->createLeadResponseDto($lead);
            $responseData = $serializer->serialize($leadResponseDto, 'json');

            return new JsonResponse($responseData, Response::HTTP_CREATED, [], true);
        } catch (\Exception $e) {
            $this->getLogger()->error('Error creating lead', [
                'exception_message' => $e->getMessage(),
            ]);

            $errorDto = new class(error: 'Internal server error', status_code: Response::HTTP_INTERNAL_SERVER_ERROR) {
                public function __construct(
                    public string $error,
                    public int $status_code,
                ) {
                }
            };

            return new JsonResponse($serializer->serialize($errorDto, 'json'), Response::HTTP_INTERNAL_SERVER_ERROR, [], true);
        }
    }
}
