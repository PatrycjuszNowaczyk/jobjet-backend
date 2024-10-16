<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/users')]
class UserController extends AbstractController
{

    /**
     * List all users
     *
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    #[Route('/', name: 'list_users', methods: ['GET'])]
    public function listUsers(EntityManagerInterface $entityManager): JsonResponse
    {
        $users = $entityManager
            ->getRepository(User::class)
            ->findAll();

        $formattedUsers = array_map(fn($user) => [
            'id' => $user->getId(),
            'email' => $user->getEmail()
        ], $users);

        return $this->json($formattedUsers);
    }

}
