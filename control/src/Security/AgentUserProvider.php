<?php

namespace DevHelm\Control\Security;

use DevHelm\Control\Repository\AgentRepositoryInterface;
use DevHelm\Control\Repository\ApiKeyRepositoryInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class AgentUserProvider implements UserProviderInterface
{
    private AgentRepositoryInterface $agentRepository;
    private ApiKeyRepositoryInterface $apiKeyRepository;

    public function __construct(
        AgentRepositoryInterface $agentRepository,
        ApiKeyRepositoryInterface $apiKeyRepository,
    ) {
        $this->agentRepository = $agentRepository;
        $this->apiKeyRepository = $apiKeyRepository;
    }

    public function loadUserByApiKey(string $apiKey): AgentUser
    {
        $apiKeyEntity = $this->apiKeyRepository->findEnabledByKey($apiKey);

        if (null === $apiKeyEntity) {
            throw new UserNotFoundException('API Key not found or expired');
        }

        $agent = $apiKeyEntity->getAgent();

        return new AgentUser($agent);
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        return $this->loadUserByApiKey($identifier);
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof AgentUser) {
            throw new UnsupportedUserException(sprintf('Invalid user class "%s".', get_class($user)));
        }

        $agent = $this->agentRepository->findById($user->getAgent()->getId());

        if (!$agent) {
            throw new UserNotFoundException('Agent no longer exists');
        }

        return new AgentUser($agent);
    }

    public function supportsClass(string $class): bool
    {
        return AgentUser::class === $class;
    }
}
