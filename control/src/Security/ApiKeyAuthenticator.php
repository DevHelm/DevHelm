<?php

namespace DevHelm\Control\Security;

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

class ApiKeyAuthenticator extends AbstractAuthenticator
{
    private AgentUserProvider $agentUserProvider;

    private const API_KEY_HEADER = 'X-API-KEY';
    private const API_KEY_QUERY_PARAM = 'api_key';

    public function __construct(AgentUserProvider $agentUserProvider)
    {
        $this->agentUserProvider = $agentUserProvider;
    }

    public function supports(Request $request): bool
    {
        return str_starts_with($request->getPathInfo(), '/api/');
    }

    public function authenticate(Request $request): Passport
    {
        $apiKey = $request->headers->get(self::API_KEY_HEADER);

        if (!$apiKey) {
            $apiKey = $request->query->get(self::API_KEY_QUERY_PARAM);
        }

        if (!$apiKey) {
            throw new CustomUserMessageAuthenticationException('API key is missing');
        }

        return new SelfValidatingPassport(
            new UserBadge($apiKey, function (string $apiKey) {
                return $this->agentUserProvider->loadUserByApiKey($apiKey);
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            'error' => 'Authentication failed',
            'message' => $exception->getMessage(),
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }
}
