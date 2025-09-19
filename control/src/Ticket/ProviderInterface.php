<?php

namespace DevHelm\Control\Ticket;

use DevHelm\Control\ValueObjects\Ticket;

interface ProviderInterface
{
    /**
     * Get the next ticket from the provider for a given project.
     *
     * @param string $project The project identifier
     *
     * @return Ticket|null The next ticket or null if none available
     */
    public function getNext(string $project): ?Ticket;
}
