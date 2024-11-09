<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/v1/api/users')]
final class UserController extends AbstractController
{
	private UserPasswordHasherInterface $passwordHasher;

	public function __construct(UserPasswordHasherInterface $passwordHasher)
	{
		$this->passwordHasher = $passwordHasher;
	}

	#[Route('', name: 'app_user_new', methods: ['POST'])]
	public function new(Request $request, EntityManagerInterface $entityManager, Security $security): JsonResponse
	{
		try {
			if (!$security->isGranted('ROLE_ADMIN')) {
				return new JsonResponse(['error' => 'You do not have permission to create a user.'], Response::HTTP_FORBIDDEN);
			}

			$data = json_decode($request->getContent(), true);

			if (!isset($data['login'], $data['phone'], $data['password'])) {
				return new JsonResponse(['error' => 'Login, phone, password, and id are required.'], Response::HTTP_BAD_REQUEST);
			}

			$user = new User();
			$user
				->setLogin($data['login'])
				->setPhone($data['phone'])
				->setRoles(explode(' ', $data['role']) ?? ['ROLE_USER']);

			$hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
			$user->setPassword($hashedPassword);

			$entityManager->persist($user);
			$entityManager->flush();

			return new JsonResponse([
				'id' => $user->getId(),
				'login' => $user->getLogin(),
				'phone' => $user->getPhone(),
				'password' => $user->getPassword()
			], Response::HTTP_CREATED);
		} catch (UniqueConstraintViolationException $e) {
			return new JsonResponse(['error' => 'A user with this id, login or password already exists.'], Response::HTTP_CONFLICT);
		} catch (\Exception $e) {
			return new JsonResponse(['error' => 'An error occurred: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
		}
	}

	#[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
	public function show(User $user, Security $security): JsonResponse
	{
		try {
			if (!$security->isGranted('ROLE_ADMIN') && $security->getUser()->getId() !== $user->getId()) {
				$currentUser = $security->getUser();
				return new JsonResponse([
					'error' => 'You can only view your own user.',
					'current_user' => [
						'id' => $currentUser->getId(),
						'login' => $currentUser->getLogin(),
						'phone' => $currentUser->getPhone(),
						'password' => $currentUser->getPassword()
					]
				], Response::HTTP_FORBIDDEN);
			}

			return new JsonResponse([
				'login' => $user->getLogin(),
				'phone' => $user->getPhone(),
				'password' => $user->getPassword()
			]);
		} catch (\Exception $e) {
			return new JsonResponse(['error' => 'An error occurred: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
		}
	}

	#[Route('/{id}', name: 'app_user_edit', methods: ['PUT'])]
	public function edit(Request $request, User $user, EntityManagerInterface $entityManager, Security $security): JsonResponse
	{
		try {
			if ($security->getUser()->getId() !== $user->getId() && !in_array('ROLE_ADMIN', $security->getUser()->getRoles(), true)) {
				return new JsonResponse(['error' => 'You do not have permission to edit this user.'], Response::HTTP_FORBIDDEN);
			}

			$data = json_decode($request->getContent(), true);

			if (!isset($data['login'], $data['phone'], $data['password'])) {
				return new JsonResponse(['error' => 'Login, phone, and password are required.'], Response::HTTP_BAD_REQUEST);
			}

			$user->setLogin($data['login'])
				->setPhone($data['phone']);

			// Хешуємо новий пароль перед збереженням
			$hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
			$user->setPassword($hashedPassword);

			if ($security->isGranted('ROLE_ADMIN') && isset($data['role'])) {
				$user->setRoles(explode(' ', $data['role']) ?? ['ROLE_USER']);
			}

			$entityManager->flush();

			return new JsonResponse([
				'id' => $user->getId(),
			]);
		} catch (UniqueConstraintViolationException $e) {
			return new JsonResponse(['error' => 'A user with this login or password already exists.'], Response::HTTP_CONFLICT);
		} catch (\Exception $e) {
			return new JsonResponse(['error' => 'An error occurred: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
		}
	}

	#[Route('/{id}', name: 'app_user_delete', methods: ['DELETE'])]
	public function delete(User $user, EntityManagerInterface $entityManager, Security $security): JsonResponse
	{
		try {
			if (!in_array('ROLE_ADMIN', $security->getUser()->getRoles(), true)) {
				return new JsonResponse(['error' => 'You do not have permission to delete this user.'], Response::HTTP_FORBIDDEN);
			}

			$entityManager->remove($user);
			$entityManager->flush();

			return new JsonResponse(['message' => 'User deleted successfully.'], Response::HTTP_OK);
		} catch (\Exception $e) {
			return new JsonResponse(['error' => 'An error occurred: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
		}
	}
}

