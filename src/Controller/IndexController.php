<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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
    phpinfo();
    return new Response();
  }

  /**
   * Route not found
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  #[Route('/{path}', name: 'error', requirements: ['path' => '.*'])]
  public function error(): JsonResponse {
    return $this->json(['message' => 'Api route not found!'], Response::HTTP_NOT_FOUND);
  }
}
