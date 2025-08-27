<?php

namespace DevHelm\Control\Repository;

use DevHelm\Control\Entity\Agent;
use DevHelm\Control\Entity\Team;
use Parthenon\Athena\Repository\CrudRepositoryInterface;

interface AgentRepositoryInterface extends CrudRepositoryInterface
{
    public function findByTeam(Team $team): array;

    public function findByName(string $name): ?Agent;
}
