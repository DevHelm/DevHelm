<?php

namespace DevHelm\Control\Repository;

use Parthenon\Athena\Repository\CrudRepositoryInterface;

interface ApiKeyRepositoryInterface extends CrudRepositoryInterface
{
    public function findEnabledByKey(string $key): ?\DevHelm\Control\Entity\ApiKey;
}
