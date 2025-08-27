<?php

namespace DevHelm\Control\Repository\Orm;

use DevHelm\Control\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Parthenon\Common\Repository\CustomServiceRepository;

class UserRepository extends CustomServiceRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }
}
