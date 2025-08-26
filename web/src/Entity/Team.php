<?php

namespace DevHelm\Control\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Parthenon\Common\Address;

#[ORM\Entity]
#[ORM\Table('teams')]
class Team extends \Parthenon\User\Entity\Team
{
    #[ORM\OneToMany(mappedBy: 'team', targetEntity: User::class)]
    protected Collection $members;


    public function getMembers(): array
    {
        return $this->members->toArray();
    }

    public function setMembers(Collection $members): void
    {
        $this->members = $members;
    }

    public function getDisplayName(): string
    {
        return $this->getBillingEmail();
    }

    public function hasBillingAddress(): bool
    {
        return isset($this->billingAddress);
    }

    public function hasExternalCustomerReference(): bool
    {
        return isset($this->externalCustomerReference);
    }
}
