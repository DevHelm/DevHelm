<?php

/*
 * Copyright 2024 all rights reserved
 */

namespace DevHelm\Control\Repository;

use Parthenon\Billing\Repository\CustomerRepositoryInterface;
use Parthenon\Payments\Repository\SubscriberRepositoryInterface;

interface TeamRepositoryInterface extends \Parthenon\User\Repository\TeamRepositoryInterface, SubscriberRepositoryInterface, CustomerRepositoryInterface
{
}
