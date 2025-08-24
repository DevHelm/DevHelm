<?php

namespace App\Repository;

use App\Entity\Agent;
use App\Entity\Team;
use Parthenon\Athena\Repository\DoctrineCrudRepository;

class AgentRepository extends DoctrineCrudRepository implements AgentRepositoryInterface
{
    public function findByTeam(Team $team): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.team = :team')
            ->setParameter('team', $team)
            ->getQuery()
            ->getResult();
    }

    public function findByName(string $name): ?Agent
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
