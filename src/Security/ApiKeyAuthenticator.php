<?php
declare(strict_types=1);

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

final class ApiKeyAuthenticator extends AbstractAuthenticator
{
  public function supports(Request $request): ?bool
  {
    $expected = $_ENV['API_KEY'] ?? '';
    if ($expected === '') {
      // No API key configured → do not engage authenticator
      return false;
    }
    return str_starts_with($request->getPathInfo(), '/products');
  }

  public function authenticate(Request $request): Passport
  {
    $provided = $request->headers->get('X-API-Key', '');
    $expected = $_ENV['API_KEY'] ?? '';
    if ($expected !== '' && hash_equals($expected, $provided)) {
      return new \Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport(
        new \Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge('api-key')
      );
    }
    throw new AuthenticationException('Invalid API key');
  }

  public function onAuthenticationFailure(Request $r, AuthenticationException $e): ?JsonResponse
  {
    return new JsonResponse(['error' => 'Unauthorized'], 401);
  }
  public function onAuthenticationSuccess(Request $r, TokenInterface $t, string $f): ?JsonResponse
  {
    return null;
  }
}