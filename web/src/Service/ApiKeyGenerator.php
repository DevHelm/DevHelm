<?php

namespace App\Service;

use App\Entity\Agent;
use App\Entity\ApiKey;
use App\Repository\ApiKeyRepositoryInterface;

/**
 * Service for generating API keys for agents.
 */
class ApiKeyGenerator
{
    public function __construct(
        private ApiKeyRepositoryInterface $apiKeyRepository,
    ) {
    }

    /**
     * Generate and save an API key for the given agent.
     *
     * @param Agent $agent The agent to generate an API key for
     *
     * @return ApiKey The generated API key
     */
    public function generateForAgent(Agent $agent): ApiKey
    {
        // Generate a random key with high entropy
        $key = bin2hex(random_bytes(32));

        // Create a new API key entity
        $apiKey = new ApiKey();
        $apiKey->setKey($key);
        $apiKey->setAgent($agent);

        // Associate the API key with the agent
        $agent->addApiKey($apiKey);

        // Save the API key using the repository
        $this->apiKeyRepository->save($apiKey);

        return $apiKey;
    }
}
