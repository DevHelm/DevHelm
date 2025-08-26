<?php

namespace App\Repository;

use Parthenon\Athena\Repository\DoctrineCrudRepository;

class ApiKeyRepository extends DoctrineCrudRepository implements ApiKeyRepositoryInterface
{
    public function findEnabledByKey(string $key): ?\App\Entity\ApiKey
    {
        $qb = $this->createQueryBuilder('ak');

        $qb->where('ak.key = :key')
           ->andWhere('ak.status = :status')
           ->andWhere('ak.deletedAt IS NULL')
           ->andWhere('(ak.expiresAt IS NULL OR ak.expiresAt > :now)')
           ->setParameter('key', $key)
           ->setParameter('status', 'active')
           ->setParameter('now', new \DateTimeImmutable('now'));

        return $qb->getQuery()->getOneOrNullResult();
    }
}
