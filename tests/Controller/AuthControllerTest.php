<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class AuthControllerTest extends WebTestCase
{
	protected function assertJsonResponse(Response $response, array $expectedData): void
	{
		$this->assertTrue($response->headers->contains('Content-Type', 'application/json'), 'Response is not in JSON format');
		$this->assertJsonStringEqualsJsonString(json_encode($expectedData), $response->getContent());
	}

	public function testLoginWithValidCredentials(): void
	{
		$client = static::createClient();

		$data = [
			'login' => 'testuser',
			'password' => '123456',
		];

		$client->request('POST', '/api/login', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));

		$this->assertResponseStatusCodeSame(Response::HTTP_OK);

		$response = $client->getResponse();
		$responseData = json_decode($response->getContent(), true);
		$token = $responseData['token'] ?? null;

		$this->assertNotNull($token, 'Token is missing');
	}

	public function testLoginWithInvalidCredentials(): void
	{
		$client = static::createClient();

		$data = [
			'login' => 'invaliduser',
			'password' => 'wrongpassword',
		];

		$client->request('POST', '/api/login', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));

		$this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
		$this->assertJsonResponse($client->getResponse(), [
			'message' => 'Invalid credentials',
		]);
	}
}
