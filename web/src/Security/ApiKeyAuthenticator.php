<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

/**
 * Authenticator for API key-based authentication.
 */
class ApiKeyAuthenticator extends AbstractAuthenticator
{
    private AgentUserProvider $agentUserProvider;
    
    // API key can be provided in header or as a query parameter
    private const API_KEY_HEADER = 'X-API-KEY';
    private const API_KEY_QUERY_PARAM = 'api_key';

    public function __construct(AgentUserProvider $agentUserProvider)
    {
        $this->agentUserProvider = $agentUserProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Request $request): bool
    {
        // Only support API routes
        return str_starts_with($request->getPathInfo(), '/api/');
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(Request $request): Passport
    {
        // Extract API key from request (header or query param)
        $apiKey = $request->headers->get(self::API_KEY_HEADER);
        
        if (!$apiKey) {
            $apiKey = $request->query->get(self::API_KEY_QUERY_PARAM);
        }

        if (!$apiKey) {
            throw new CustomUserMessageAuthenticationException('API key is missing');
        }

        // Create a user badge that will load the user via the API key
        return new SelfValidatingPassport(
            new UserBadge($apiKey, function (string $apiKey) {
                return $this->agentUserProvider->loadUserByApiKey($apiKey);
            })
        );
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // On success, continue with the request
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            'error' => 'Authentication failed',
            'message' => $exception->getMessage()
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }
}