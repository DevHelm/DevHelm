<?php

namespace App\Repository;

use App\Entity\Agent;
use App\Entity\Team;
use Parthenon\Athena\Repository\CrudRepositoryInterface;

interface AgentRepositoryInterface extends CrudRepositoryInterface
{
    public function findByTeam(Team $team): array;

    public function findByName(string $name): ?Agent;
}