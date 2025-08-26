<?php

namespace DevHelm\Control\Repository;

use DevHelm\Control\Entity\InviteCode;
use DevHelm\Control\Entity\Team;

class InviteCodeRepository extends \Parthenon\User\Repository\InviteCodeRepository implements InviteCodeRepositoryInterface
{
    /**
     * @return InviteCode[]
     */
    public function findAllUnusedInvitesForTeam(Team $team): array
    {
        return $this->entityRepository->findBy(['team' => $team, 'used' => false]);
    }

    public function getUsableInviteCount(Team $team): int
    {
        return $this->entityRepository->count(['team' => $team, 'used' => false, 'cancelled' => false]);
    }
}
