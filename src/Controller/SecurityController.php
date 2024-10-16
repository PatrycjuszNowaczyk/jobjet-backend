<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

class SecurityController extends AbstractController
{

    /**
     * Register new user
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface $userPasswordHasher
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    #[Route('/register', name: 'register_user', methods: ['POST'])]
    public function register(
      Request $request,
      EntityManagerInterface $entityManager,
      UserPasswordHasherInterface $userPasswordHasher
    ): JsonResponse {
        $requestHeaderAccept = $request->headers->get('Accept');
        $requestHeaderContentType = $request->headers->get('Content-Type');

        if ($requestHeaderAccept !== 'application/json') {
            throw new BadRequestHttpException(
              'Wrong HTTP header \'Accept\'!'
            );
        }
        if ($requestHeaderContentType !== 'application/x-www-form-urlencoded') {
            throw new BadRequestHttpException(
              'Wrong HTTP header \'Content-Type\'!'
            );
        }

        $data = $request->request->all();

        $user = new User();

        $userEmail = $data['email'] ?? null;
        $userPassword = $data['password'] ?? null;

        if (!$userEmail) {
            throw new BadRequestHttpException(
              'Email is missing!'
            );
        }
        if (!$userPassword) {
            throw new BadRequestHttpException(
              'Password is missing!'
            );
        }

        $isUserEmailTaken = $entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => $userEmail]) !== null;
        if ($isUserEmailTaken) {
            throw new BadRequestHttpException(
              'User already exists!'
            );
        }

        $user->setEmail($userEmail);
        $user->setPassword(
          $userPasswordHasher->hashPassword(
            $user,
            $userPassword
          )
        );

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json([
          'message' => 'New user registered!',
        ], 201);
    }

    /**
     * Login users
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface $userPasswordHasher
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    #[Route('/login', name: 'login_user', methods: ['POST'])]
    public function login(
      Request $request,
      EntityManagerInterface $entityManager,
      UserPasswordHasherInterface $userPasswordHasher
    ): JsonResponse {
        $requestHeaderAccept = $request->headers->get('Accept');
        $requestHeaderContentType = $request->headers->get('Content-Type');

        if ($requestHeaderAccept !== 'application/json') {
            throw new BadRequestHttpException(
              'Wrong HTTP header \'Accept\'!'
            );
        }
        if ($requestHeaderContentType !== 'application/x-www-form-urlencoded') {
            throw new BadRequestHttpException(
              'Wrong HTTP header \'Content-Type\'!'
            );
        }

        $data = $request->request->all();

        $userEmail = $data['email'] ?? null;
        $userPassword = $data['password'] ?? null;

        if (!$userEmail) {
            throw new BadRequestHttpException(
              'Email is missing!'
            );
        }
        if (!$userPassword) {
            throw new BadRequestHttpException(
              'Password is missing!'
            );
        }

        $user = $entityManager
          ->getRepository(User::class)
          ->findOneBy(['email' => $userEmail]);
        if (!$user) {
            throw new BadRequestHttpException(
              'User not found!'
            );
        }

        if (!$userPasswordHasher->isPasswordValid($user, $userPassword)) {
            throw new BadRequestHttpException(
              'Wrong password!'
            );
        }

        return $this->json([
          'message' => 'User logged in!',
        ]);
    }

    #[Route('/logout', name: 'logout_user', methods: ['POST'])]
    public function logout(): JsonResponse {
        $user = $this->getUser();

        if (!$user) {
            throw new AuthenticationCredentialsNotFoundException(
              'User is not logged in!'
            );
        }

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
              $params["path"], $params["domain"],
              $params["secure"], $params["httponly"]
            );
        }

        session_destroy();

        return $this->json([
          'message' => 'User logged out!',
        ]);
    }

}
