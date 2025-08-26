<?php

namespace DevHelm\Control\Repository\Orm;

use DevHelm\Control\Entity\Feedback;
use Doctrine\Persistence\ManagerRegistry;
use Parthenon\Common\Repository\CustomServiceRepository;

class FeedbackRepository extends CustomServiceRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Feedback::class);
    }
}
