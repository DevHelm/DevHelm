<?php

namespace App\Repository;

use Parthenon\Athena\Repository\DoctrineCrudRepository;

class ApiKeyRepository extends DoctrineCrudRepository implements ApiKeyRepositoryInterface
{
    /**
     * Find an enabled API key by its key value.
     *
     * @param string $key The API key to look for
     * @return \App\Entity\ApiKey|null The API key entity if found and enabled, null otherwise
     */
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
