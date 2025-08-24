<?php

namespace App\Repository;

use App\Entity\Agent;
use App\Entity\Team;
use Parthenon\Athena\Repository\DoctrineCrudRepository;

class AgentRepository extends DoctrineCrudRepository implements AgentRepositoryInterface
{
}
