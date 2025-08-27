<?php

namespace Test\DevHelm\Control\Unit\Security;

use DevHelm\Control\Security\AgentUserProvider;
use DevHelm\Control\Security\ApiKeyAuthenticator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class ApiKeyAuthenticatorTest extends TestCase
{
    private ApiKeyAuthenticator $authenticator;
    private MockObject|AgentUserProvider $agentUserProvider;

    protected function setUp(): void
    {
        $this->agentUserProvider = $this->createMock(AgentUserProvider::class);
        $this->authenticator = new ApiKeyAuthenticator($this->agentUserProvider);
    }

    #[DataProvider('provideSupportsData')]
    public function testSupports(string $path, bool $expected): void
    {
        $request = Request::create($path);

        $this->assertEquals($expected, $this->authenticator->supports($request));
    }

    public static function provideSupportsData(): array
    {
        return [
            'api path' => ['/api/v1/hello-world', true],
            'api subpath' => ['/api/other/path', true],
            'non-api path' => ['/app/dashboard', false],
            'root path' => ['/', false],
        ];
    }

    public function testAuthenticateWithHeaderApiKey(): void
    {
        // Create a request with API key in header
        $request = Request::create('/api/v1/endpoint');
        $request->headers->set('X-API-KEY', 'test-api-key');

        // Call the authenticate method
        $passport = $this->authenticator->authenticate($request);
        $this->assertInstanceOf(SelfValidatingPassport::class, $passport);
    }

    public function testAuthenticateWithMissingApiKey(): void
    {
        // Create a request without API key
        $request = Request::create('/api/v1/endpoint');

        // Expect exception
        $this->expectException(CustomUserMessageAuthenticationException::class);
        $this->expectExceptionMessage('API key is missing');

        // Call the authenticate method
        $this->authenticator->authenticate($request);
    }

    public function testOnAuthenticationSuccess(): void
    {
        $request = $this->createMock(Request::class);
        $token = $this->createMock(TokenInterface::class);

        // On success, the method should return null to continue with the request
        $response = $this->authenticator->onAuthenticationSuccess($request, $token, 'api');

        $this->assertNull($response);
    }

    public function testOnAuthenticationFailure(): void
    {
        $request = $this->createMock(Request::class);
        $exception = new AuthenticationException('Authentication failed test');

        // Call the method
        $response = $this->authenticator->onAuthenticationFailure($request, $exception);

        // Verify response
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(401, $response->getStatusCode());

        // Verify response content
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $content);
        $this->assertEquals('Authentication failed', $content['error']);
        $this->assertArrayHasKey('message', $content);
        $this->assertEquals('Authentication failed test', $content['message']);
    }
}
