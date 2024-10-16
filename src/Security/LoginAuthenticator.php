<?php

namespace App\Security;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

/**
 * @see https://symfony.com/doc/current/security/custom_authenticator.html
 */
class LoginAuthenticator extends AbstractAuthenticator
{

    private UserRepository $userRepository;
    private UserPasswordHasher $userPasswordHasher;

    public function __construct(
      UserRepository $userRepository,
      UserPasswordHasherInterface $userPasswordHasher
    )
    {
        $this->userRepository = $userRepository;
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function supports(Request $request): bool
    {
        return (
          $request->attributes->get('_route') === 'login_user'
          && $request->isMethod(Request::METHOD_POST)
        );
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email');
        $password = $request->request->get('password');

        $user = $this->userRepository->findOneBy([
          'email' => $email
        ]);

        if (!($user && $this->userPasswordHasher->isPasswordValid($user, $password))) {
            throw new CustomUserMessageAuthenticationException('Invalid credentials.');
        }

         $userIdentifier = $user->getUserIdentifier();

         return new SelfValidatingPassport(new UserBadge($userIdentifier));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): RedirectResponse
    {
        // on success, redirect to homepage
        return new RedirectResponse('/');
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationFailure(
      Request $request,
      AuthenticationException $exception
    ): JsonResponse {
        return new JsonResponse(['message' => $exception->getMessage()], 401);
    }

}
