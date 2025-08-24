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

    private $session;
    private $entityManager;
    private $agentRepository;
    private $serializer;
    private $validator;
    private $agentFactory;
    private $dtoFactory;
    private $response;
    private $userContext;
    private $teamContext;

    public function __construct(
        Session $session,
        EntityManagerInterface $entityManager,
        AgentRepositoryInterface $agentRepository,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        AgentFactory $agentFactory,
        CreateAgentDtoFactory $dtoFactory
    ) {
        $this->session = $session;
        $this->entityManager = $entityManager;
        $this->agentRepository = $agentRepository;
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->agentFactory = $agentFactory;
        $this->dtoFactory = $dtoFactory;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(\Behat\Behat\Hook\Scope\BeforeScenarioScope $scope)
    {
        $environment = $scope->getEnvironment();
        $this->userContext = $environment->getContext(UserContext::class);
        $this->teamContext = $environment->getContext(TeamContext::class);
    }

    /**
     * @When I create an agent:
     */
    public function iCreateAnAgent(TableNode $table)
    {
        $data = $table->getRowsHash();
        $user = $this->userContext->getCurrentUser();
        $team = $user->getTeam();

        // Create DTO from data
        $dto = $this->dtoFactory->createFromArray([
            'name' => $data['Name'] ?? '',
            'project' => $data['Project'] ?? ''
        ]);

        // Send API request
        $this->sendJsonRequest(
            'POST',
            '/app/agents',
            [
                'name' => $dto->name,
                'project' => $dto->project
            ]
        );
        
        $this->response = $this->session->getPage()->getContent();
    }

    /**
     * @Then there should be an agent called :name
     */
    public function thereShouldBeAnAgentCalled($name)
    {
        $agent = $this->agentRepository->findByName($name);
        Assert::notNull($agent, "Agent with name '$name' was not found");
    }

    /**
     * @Then there should not be an agent called :name
     */
    public function thereShouldNotBeAnAgentCalled($name)
    {
        $agent = $this->agentRepository->findByName($name);
        Assert::null($agent, "Agent with name '$name' was found but should not exist");
    }

    /**
     * @Then there should be an validation for no project
     */
    public function thereShouldBeAnValidationForNoProject()
    {
        $responseData = json_decode($this->response, true);
        Assert::keyExists($responseData, 'errors', 'Validation errors not found in response');
        Assert::keyExists($responseData['errors'], 'project', 'No validation error for project field');
    }
}