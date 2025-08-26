<?php

namespace App\Repository;

use Parthenon\Athena\Repository\CrudRepositoryInterface;

interface ApiKeyRepositoryInterface extends CrudRepositoryInterface
{
    /**
     * Find an enabled API key by its key value.
     *
     * @param string $key The API key to look for
     *
     * @return \App\Entity\ApiKey|null The API key entity if found and enabled, null otherwise
     */
    public function findEnabledByKey(string $key): ?\App\Entity\ApiKey;
}
