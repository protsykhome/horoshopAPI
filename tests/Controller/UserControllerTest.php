<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends WebTestCase
{
	protected function assertJsonResponse(Response $response, array $expectedData): void
	{
		$this->assertTrue($response->headers->contains('Content-Type', 'application/json'), 'Response is not in JSON format');
		$this->assertJsonStringEqualsJsonString(json_encode($expectedData), $response->getContent());
	}

	public function testRegisterNewUserWithValidData(): void
	{
		$client = static::createClient();

		$data = [
			'login' => 'newuser',
			'password' => 'newpassword',
			'phone' => '1234567890',
			'role' => 'ROLE_USER',
		];

		$client->request('POST', '/v1/api/users', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));

		$this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
		$this->assertJsonResponse($client->getResponse(), [
			'message' => 'User registered successfully',
		]);
	}

	public function testRegisterUserWithExistingLogin(): void
	{
		$client = static::createClient();

		$data = [
			'login' => 'existinguser',
			'password' => 'password123',
			'phone' => '0987654321',
			'role' => 'ROLE_USER',
		];

		$client->request('POST', '/v1/api/users', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));

		$this->assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
		$this->assertJsonResponse($client->getResponse(), [
			'error' => 'User with this login already exists.',
		]);
	}
}
