<?php

namespace App\Tests\Unit\Controller\Api;

use App\Entity\Agent;
use App\Security\AgentUser;
use DevHelm\Control\Controller\Api\HelloWorldController;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;

class HelloWorldControllerTest extends TestCase
{
    private HelloWorldController $controller;
    private MockObject|Security $security;
    
    protected function setUp(): void
    {
        $this->security = $this->createMock(Security::class);
        $this->controller = new HelloWorldController($this->security);
    }
    
    public function testHelloWorldWithoutAuthentication(): void
    {
        // Configure security to return null (no authenticated user)
        $this->security->method('getUser')
            ->willReturn(null);
        
        // Call the controller
        $response = $this->controller->helloWorld();
        
        // Verify response is a JsonResponse
        $this->assertInstanceOf(JsonResponse::class, $response);
        
        // Verify response content
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('hello', $content);
        $this->assertEquals('world', $content['hello']);
        
        // Verify no agent data is included
        $this->assertArrayNotHasKey('agent', $content);
    }
    
    public function testHelloWorldWithAuthentication(): void
    {
        // Create mock objects
        $agent = $this->createMock(Agent::class);
        $agentUser = $this->createMock(AgentUser::class);
        
        // Configure mocks
        $agent->method('getId')
            ->willReturn('agent-id-123');
        $agent->method('getName')
            ->willReturn('Test Agent');
            
        $agentUser->method('getAgent')
            ->willReturn($agent);
            
        $this->security->method('getUser')
            ->willReturn($agentUser);
        
        // Call the controller
        $response = $this->controller->helloWorld();
        
        // Verify response is a JsonResponse
        $this->assertInstanceOf(JsonResponse::class, $response);
        
        // Verify response content
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('hello', $content);
        $this->assertEquals('Test Agent', $content['hello']); // Should say hello to the agent by name
        
        // Verify agent data is included
        $this->assertArrayHasKey('agent', $content);
        $this->assertArrayHasKey('id', $content['agent']);
        $this->assertEquals('agent-id-123', $content['agent']['id']);
        $this->assertArrayHasKey('name', $content['agent']);
        $this->assertEquals('Test Agent', $content['agent']['name']);
        $this->assertArrayHasKey('authenticated', $content['agent']);
        $this->assertTrue($content['agent']['authenticated']);
    }
    
    public function testHelloWorldWithDifferentUserClass(): void
    {
        // Configure security to return a different user class
        $this->security->method('getUser')
            ->willReturn(new \stdClass());
        
        // Call the controller
        $response = $this->controller->helloWorld();
        
        // Verify response is a JsonResponse
        $this->assertInstanceOf(JsonResponse::class, $response);
        
        // Verify response content
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('hello', $content);
        $this->assertEquals('world', $content['hello']);
        
        // Verify no agent data is included
        $this->assertArrayNotHasKey('agent', $content);
    }
}