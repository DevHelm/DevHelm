<?php

namespace App\Security;

use App\Entity\Agent;
use App\Repository\AgentRepositoryInterface;
use App\Repository\ApiKeyRepositoryInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Provides Agent entities as Symfony users for API authentication.
 */
class AgentUserProvider implements UserProviderInterface
{
    private AgentRepositoryInterface $agentRepository;
    private ApiKeyRepositoryInterface $apiKeyRepository;

    public function __construct(
        AgentRepositoryInterface $agentRepository,
        ApiKeyRepositoryInterface $apiKeyRepository
    ) {
        $this->agentRepository = $agentRepository;
        $this->apiKeyRepository = $apiKeyRepository;
    }

    /**
     * Find a user by their API key.
     *
     * @param string $apiKey The API key to look up
     * @return AgentUser The user object
     * @throws UserNotFoundException If the API key is invalid or the agent is not found
     */
    public function loadUserByApiKey(string $apiKey): AgentUser
    {
        $apiKeyEntity = $this->apiKeyRepository->findEnabledByKey($apiKey);
        
        if (null === $apiKeyEntity) {
            throw new UserNotFoundException('API Key not found or expired');
        }
        
        $agent = $apiKeyEntity->getAgent();
        
        if ($agent->getStatus()->value !== 'enabled') {
            throw new UserNotFoundException('Agent is not enabled');
        }
        
        return new AgentUser($agent);
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        // This method is required by the interface but will be delegated
        // to the authenticator which will call loadUserByApiKey instead
        throw new \RuntimeException('Use loadUserByApiKey instead');
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    public function supportsClass(string $class): bool
    {
        return AgentUser::class === $class;
    }
}