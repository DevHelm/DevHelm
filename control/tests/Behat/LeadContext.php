<?php

namespace Test\DevHelm\Control\Behat;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Session;
use DevHelm\Control\Entity\Lead;
use DevHelm\Control\Repository\Orm\InviteCodeRepository;
use DevHelm\Control\Repository\Orm\LeadRepository;
use Doctrine\ORM\EntityManagerInterface;

class LeadContext implements Context
{
    use SendRequestTrait;

    public function __construct(
        private Session $session,
        private LeadRepository $leadRepository,
        private InviteCodeRepository $inviteCodeRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @When I create a lead:
     */
    public function iCreateALead(TableNode $table)
    {
        $data = [];
        foreach ($table->getRowsHash() as $field => $value) {
            $key = strtolower($field);
            $data[$key] = $value;
        }

        $this->sendJsonRequest('POST', '/app/leads', $data);
    }

    /**
     * @Then there will be a lead called :name
     */
    public function thereWillBeALeadCalled($name)
    {
        $lead = $this->leadRepository->findOneBy(['name' => $name]);

        if (!$lead) {
            throw new \Exception('No lead found with name: '.$name);
        }
    }

    /**
     * @Then there will not be a lead called :name
     */
    public function thereWillNotBeALeadCalled($name)
    {
        $lead = $this->leadRepository->findOneBy(['name' => $name]);

        if ($lead) {
            throw new \Exception('Lead found with name: '.$name);
        }
    }
}
