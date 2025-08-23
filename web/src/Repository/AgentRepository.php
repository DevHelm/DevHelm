<?php

namespace App\Repository;

use App\Entity\Agent;
use App\Entity\Team;
use Parthenon\Athena\Repository\DoctrineCrudRepository;

class AgentRepository extends DoctrineCrudRepository implements AgentRepositoryInterface
{
    public function findByTeam(Team $team): array
    {
        return $this->entityRepository->findBy(['team' => $team]);
    }

    public function findByName(string $name): ?Agent
    {
        return $this->entityRepository->findOneBy(['name' => $name]);
    }
}