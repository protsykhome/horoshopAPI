<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_LOGIN_PASSWORD', columns: ['login', 'password'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
	#[ORM\Id]
	#[ORM\GeneratedValue(strategy: "AUTO")]
	#[ORM\Column]
	private ?int $id = null;

	#[Assert\Length(min: 1, max: 8)]
	#[ORM\Column(length: 8)]
	private ?string $login = null;

	#[Assert\Length(min: 1, max: 8)]
	#[ORM\Column(length: 255)]
	private ?string $password = null;

	#[Assert\Length(min: 1, max: 8)]
	#[ORM\Column(length: 8)]
	private ?string $phone = null;

	#[ORM\Column]
	private array $roles = [];

	public function getId(): ?int
	{
		return $this->id;
	}

	public function setId(int $id): static
	{
		$this->id = $id;
		return $this;
	}

	public function getLogin(): ?string
	{
		return $this->login;
	}

	public function setLogin(string $login): static
	{
		$this->login = $login;
		return $this;
	}

	public function getPassword(): ?string
	{
		return $this->password;
	}

	public function setPassword(?string $password): static
	{
		$this->password = $password;
		return $this;
	}

	public function getPhone(): ?string
	{
		return $this->phone;
	}

	public function setPhone(?string $phone): static
	{
		$this->phone = $phone;
		return $this;
	}

	public function getUserIdentifier(): string
	{
		return (string)$this->login;
	}

	public function getRoles(): array
	{
		$roles = $this->roles;
		$roles[] = 'ROLE_USER';
		return array_unique($roles);
	}

	public function setRoles(array $roles): static
	{
		$this->roles = $roles;
		return $this;
	}

	public function eraseCredentials(): void
	{
		// If you store any temporary, sensitive data on the user, clear it here
		// $this->plainPassword = null;
	}
}
