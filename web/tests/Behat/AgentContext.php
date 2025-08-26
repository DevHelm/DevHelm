<?php

namespace Test\DevHelm\Control\Behat;

use DevHelm\Control\Repository\AgentRepositoryInterface;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Session;
use Doctrine\ORM\EntityManagerInterface;

class AgentContext implements Context
{
    use SendRequestTrait;
    use TeamTrait;


    public function __construct(
        private Session $session,
        private EntityManagerInterface $entityManager,
        private AgentRepositoryInterface $agentRepository,
    ) {
    }


    /**
     * @When I create an agent:
     */
    public function iCreateAnAgent(TableNode $table)
    {
        $data = $table->getRowsHash();
        // Send API request
        $this->sendJsonRequest(
            'POST',
            '/app/agents',
            [
                'name' => $data['Name'],
                'project' => $data['Project']
            ]
        );
    }

    /**
     * @Then there should be an agent called :name
     */
    public function thereShouldBeAnAgentCalled($name)
    {
        $agent = $this->agentRepository->findByName($name);

        if (!$agent) {
            var_dump($this->getJsonContent());
            throw new \Exception("Agent with name '$name' was not found");
        }
    }

    /**
     * @Then there should not be an agent called :name
     */
    public function thereShouldNotBeAnAgentCalled($name)
    {
        $agent = $this->agentRepository->findByName($name);
        if ($agent) {
            throw new \Exception("Agent with name '$name' was found");
        }
    }

    /**
     * @Then there should be an validation for no project
     */
    public function thereShouldBeAnValidationForNoProject()
    {
        $responseData = $this->getJsonContent();
        if (!isset($responseData['errors'])) {
            throw new \Exception('No validation errors found in response');
        }
    }
    
    /**
     * @Then there will be an API key for the agent called :name
     */
    public function thereWillBeAnApiKeyForTheAgentCalled($name)
    {
        $agent = $this->agentRepository->findByName($name);
        
        if (!$agent) {
            throw new \Exception("Agent with name '$name' was not found");
        }
        
        // Refresh the entity to ensure we have the latest data
        $this->entityManager->refresh($agent);
        
        $apiKeys = $agent->getApiKeys();
        
        if (empty($apiKeys)) {
            throw new \Exception("No API keys found for agent with name '$name'");
        }
    }
}