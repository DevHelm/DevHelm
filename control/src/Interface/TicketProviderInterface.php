<?php

namespace DevHelm\Control\Interface;

use DevHelm\Control\ValueObject\Ticket;

interface TicketProviderInterface
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
