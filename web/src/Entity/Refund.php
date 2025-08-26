<?php

namespace DevHelm\Control\Entity;

namespace DevHelm\Control\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity()]
#[ORM\Table(name: 'refund')]
class Refund extends \Parthenon\Billing\Entity\Refund
{
}
