<?php

namespace DevHelm\Control\Repository\Orm;

use DevHelm\Control\Entity\Team;
use Doctrine\Persistence\ManagerRegistry;
use Parthenon\Common\Repository\CustomServiceRepository;

class TeamRepository extends CustomServiceRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Team::class);
    }
}
