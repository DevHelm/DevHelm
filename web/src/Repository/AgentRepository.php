<?php

namespace DevHelm\Control\Repository;

use DevHelm\Control\Entity\Agent;
use DevHelm\Control\Entity\Team;
use Parthenon\Athena\Repository\DoctrineCrudRepository;

class AgentRepository extends DoctrineCrudRepository implements AgentRepositoryInterface
{
    public function findByTeam(Team $team): array
    {
        return $this->entityRepository->findBy(['team' => $team]);
    }

    public function findByName(string $name): ?Agent
    {
        $agent = $this->entityRepository->findOneBy(['name' => $name]);

        if (!$agent instanceof Agent) {
            return null;
        }

        return $agent;
    }
}
