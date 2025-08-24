<?php

namespace App\Service;

use App\Entity\Agent;
use App\Entity\ApiKey;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service for generating API keys for agents.
 */
class ApiKeyGenerator
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Generate and save an API key for the given agent.
     *
     * @param Agent $agent The agent to generate an API key for
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
        
        // Persist the API key
        $this->entityManager->persist($apiKey);
        $this->entityManager->flush();
        
        return $apiKey;
    }
}