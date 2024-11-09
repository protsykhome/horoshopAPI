<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationController extends AbstractController
{
	private readonly UserRepository $userRepository;
	private readonly EntityManagerInterface $em;
	private readonly UserPasswordHasherInterface $passwordHasher;

	public function __construct(
		UserRepository $userRepository,
		EntityManagerInterface $em,
		UserPasswordHasherInterface $passwordHasher
	) {
		$this->userRepository = $userRepository;
		$this->em = $em;
		$this->passwordHasher = $passwordHasher;
	}

	#[Route('/registration', name: 'app_registration', methods: ['POST'])]
	public function index(Request $request): Response
	{
		$data = json_decode($request->getContent(), true);

		if ($this->userRepository->findOneBy(['login' => $data['login']])) {
			return new JsonResponse(['error' => 'User already exists'], Response::HTTP_BAD_REQUEST);
		}

		$user = new User();
		$user
			->setLogin($data['login'])
			->setPhone($data['phone']);

		$hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
		$user->setPassword($hashedPassword);

		$role = isset($data['role']) && $data['role'] === 'ROLE_ADMIN' ? 'ROLE_ADMIN' : 'ROLE_USER';
		$user->setRoles([$role]);

		$this->em->persist($user);
		$this->em->flush();

		return new JsonResponse(['message' => 'User registered successfully'], Response::HTTP_CREATED);
	}
}

