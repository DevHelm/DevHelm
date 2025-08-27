<?php

namespace DevHelm\Control\Repository\Orm;

use DevHelm\Control\Entity\ApiKey;
use Doctrine\Persistence\ManagerRegistry;
use Parthenon\Common\Repository\CustomServiceRepository;

class ApiKeyRepository extends CustomServiceRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApiKey::class);
    }
}
