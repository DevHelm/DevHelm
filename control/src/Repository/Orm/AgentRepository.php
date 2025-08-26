<?php

namespace DevHelm\Control\Repository\Orm;

use DevHelm\Control\Entity\Agent;
use Doctrine\Persistence\ManagerRegistry;
use Parthenon\Common\Repository\CustomServiceRepository;

class AgentRepository extends CustomServiceRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Agent::class);
    }
}
