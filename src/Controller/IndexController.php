<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class IndexController extends AbstractController {

  /**
   * Index route for welcome message
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  #[Route('/', name: 'index', methods: ['GET'])]
  public function index(): JsonResponse {
    return $this->json([
      'message' => 'Welcome to our api!',
      'path' => 'src/Controller/ApiController.php',
    ]);
  }

  /**
   * This is a test route for testing the api
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  #[Route('/test', name: 'test', methods: ['GET'])]
  public function test(Request $request): JsonResponse {
    $name = $request->query->get('name');
    return $this->json([
      'message' => "Hello " . ($name ? ucfirst("$name ") : '') . "from test!",
      'path' => 'src/Controller/ApiController.php',
    ]);
  }

  /**
   * Show phpinfo
   * @return \Symfony\Component\HttpFoundation\Response
   */
  #[Route('/info', name: 'info', methods: ['GET'])]
  public function info(): Response {
    $user = $this->getUser();
    $username = $user instanceof UserInterface ? $user->getEmail() : 'Guest';

    $message = "You are not logged in!";

    if ($this->getUser() instanceof UserInterface) {
      $message = "You are logged in as $username";
    }

    echo $message;

    phpinfo();

    return new Response();
  }

  /**
   * Route not found
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  #[Route('/{path}', name: 'fallback', requirements: ['path' => '.*'], priority: -1000)]
  public function error(): JsonResponse {
    return $this->json(['message' => 'Api route not found!'], Response::HTTP_NOT_FOUND);
  }
}
