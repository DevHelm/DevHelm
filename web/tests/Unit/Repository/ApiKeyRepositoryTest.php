<?php

namespace App\Tests\Unit\Repository;

use App\Entity\ApiKey;
use App\Repository\ApiKeyRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ApiKeyRepositoryTest extends TestCase
{
    private ApiKeyRepository $repository;
    private MockObject|EntityManagerInterface $entityManager;
    private MockObject|QueryBuilder $queryBuilder;
    private MockObject|AbstractQuery $query;
    
    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->queryBuilder = $this->createMock(QueryBuilder::class);
        $this->query = $this->createMock(AbstractQuery::class);
        
        // Setup repository with mocked entity manager
        $this->repository = new ApiKeyRepository($this->entityManager);
        
        // Use reflection to set the entity class since we're not calling the parent constructor
        $reflection = new \ReflectionProperty(ApiKeyRepository::class, 'entityClass');
        $reflection->setAccessible(true);
        $reflection->setValue($this->repository, ApiKey::class);
        
        // Mock createQueryBuilder method
        $reflection = new \ReflectionMethod(ApiKeyRepository::class, 'createQueryBuilder');
        $reflection->setAccessible(true);
        
        // Replace createQueryBuilder with a method that returns our mock
        $repositoryMock = $this->getMockBuilder(ApiKeyRepository::class)
            ->setConstructorArgs([$this->entityManager])
            ->onlyMethods(['createQueryBuilder'])
            ->getMock();
        
        $repositoryMock->method('createQueryBuilder')
            ->willReturn($this->queryBuilder);
        
        // Use the mock for testing findEnabledByKey
        $this->repository = $repositoryMock;
    }
    
    public function testFindEnabledByKeyWithValidKey(): void
    {
        // Setup query builder expectations
        $this->queryBuilder->expects($this->once())
            ->method('where')
            ->with('ak.key = :key')
            ->willReturnSelf();
            
        $this->queryBuilder->expects($this->exactly(3))
            ->method('andWhere')
            ->withConsecutive(
                ['ak.status = :status'],
                ['ak.deletedAt IS NULL'],
                ['(ak.expiresAt IS NULL OR ak.expiresAt > :now)']
            )
            ->willReturnSelf();
            
        $this->queryBuilder->expects($this->exactly(3))
            ->method('setParameter')
            ->withConsecutive(
                ['key', 'valid-api-key'],
                ['status', 'active'],
                ['now', $this->isInstanceOf(\DateTimeImmutable::class)]
            )
            ->willReturnSelf();
            
        $this->queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($this->query);
            
        // Create a mock API key to return
        $apiKey = $this->createMock(ApiKey::class);
        
        $this->query->expects($this->once())
            ->method('getOneOrNullResult')
            ->willReturn($apiKey);
        
        // Call the method under test
        $result = $this->repository->findEnabledByKey('valid-api-key');
        
        // Verify the result
        $this->assertSame($apiKey, $result);
    }
    
    public function testFindEnabledByKeyWithInvalidKey(): void
    {
        // Setup query builder expectations
        $this->queryBuilder->expects($this->once())
            ->method('where')
            ->willReturnSelf();
            
        $this->queryBuilder->expects($this->exactly(3))
            ->method('andWhere')
            ->willReturnSelf();
            
        $this->queryBuilder->expects($this->exactly(3))
            ->method('setParameter')
            ->willReturnSelf();
            
        $this->queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($this->query);
            
        $this->query->expects($this->once())
            ->method('getOneOrNullResult')
            ->willReturn(null);
        
        // Call the method under test
        $result = $this->repository->findEnabledByKey('invalid-api-key');
        
        // Verify the result
        $this->assertNull($result);
    }
}