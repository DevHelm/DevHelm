<?php

namespace DevHelm\Control\Repository\Orm;

use DevHelm\Control\Entity\Lead;
use Doctrine\Persistence\ManagerRegistry;
use Parthenon\Common\Repository\CustomServiceRepository;

class LeadRepository extends CustomServiceRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lead::class);
    }
}
