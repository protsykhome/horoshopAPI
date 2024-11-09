<?php
namespace App\Controller;

use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthController extends AbstractController
{
	private readonly JWTTokenManagerInterface $JWTTokenManager;
	private readonly UserPasswordHasherInterface $passwordHasher;

	public function __construct(
		JWTTokenManagerInterface $JWTTokenManager,
		UserPasswordHasherInterface $passwordHasher
	) {
		$this->JWTTokenManager = $JWTTokenManager;
		$this->passwordHasher = $passwordHasher;
	}

	#[Route('/api/login', name: 'app_auth')]
	public function index(Request $request, UserRepository $userRepository): Response
	{
		$data = json_decode($request->getContent(), true);
		$login = $data['login'];
		$password = $data['password'];

		$user = $userRepository->findOneBy(['login' => $login]);

		if (!$user) {
			return new JsonResponse(['message' => 'Invalid credentials'], Response::HTTP_UNAUTHORIZED);
		}

		if (!$this->passwordHasher->isPasswordValid($user, $password)) {
			return new JsonResponse(['message' => 'Invalid credentials'], Response::HTTP_UNAUTHORIZED);
		}

		$token = $this->JWTTokenManager->create($user);

		return new JsonResponse(['token' => $token]);
	}
}
