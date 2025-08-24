<?php

namespace App\Tests\Behat;

use App\Dto\App\Request\CreateAgentDto;
use App\Entity\Agent;
use App\Entity\Team;
use App\Factory\AgentFactory;
use App\Factory\CreateAgentDtoFactory;
use App\Repository\AgentRepositoryInterface;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Session;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

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
}