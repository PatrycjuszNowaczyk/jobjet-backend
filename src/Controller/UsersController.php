<?php

namespace App\Controller;

use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[Route('/users')]
class UsersController extends AbstractController
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

        $user = new Users();

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
            ->getRepository(Users::class)
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

}
