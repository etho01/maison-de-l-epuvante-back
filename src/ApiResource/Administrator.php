<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'Administrator',
    operations: [
        new GetCollection(
            uriTemplate: '/administrators',
            security: "is_granted('ROLE_ADMIN')",
            controller: \App\Controller\GetAdministratorsController::class,
            normalizationContext: ['groups' => ['admin:read', 'admin:list']]
        ),
        new Get(
            uriTemplate: '/administrators/{id}',
            security: "is_granted('ROLE_ADMIN')",
            controller: \App\Controller\GetAdministratorController::class,
            normalizationContext: ['groups' => ['admin:read', 'admin:detail']]
        ),
        new Post(
            uriTemplate: '/administrators',
            security: "is_granted('ROLE_ADMIN')",
            controller: \App\Controller\CreateAdministratorController::class,
            validationContext: ['groups' => ['Default', 'admin:create']],
            denormalizationContext: ['groups' => ['admin:write']],
        ),
        new Put(
            uriTemplate: '/administrators/{id}',
            security: "is_granted('ROLE_ADMIN')",
            controller: \App\Controller\UpdateAdministratorController::class,
            denormalizationContext: ['groups' => ['admin:write']],
        ),
        new Patch(
            uriTemplate: '/administrators/{id}',
            security: "is_granted('ROLE_ADMIN')",
            controller: \App\Controller\UpdateAdministratorController::class,
            denormalizationContext: ['groups' => ['admin:write']],
        ),
        new Delete(
            uriTemplate: '/administrators/{id}',
            security: "is_granted('ROLE_ADMIN')",
            controller: \App\Controller\DeleteAdministratorController::class,
        ),
    ]
)]
class Administrator
{
    public ?int $id = null;

    #[Assert\NotBlank(groups: ['admin:create'])]
    #[Assert\Email]
    public ?string $email = null;

    #[Assert\NotBlank(groups: ['admin:create'])]
    #[Assert\Length(min: 6)]
    public ?string $password = null;

    public ?string $firstName = null;

    public ?string $lastName = null;

    public array $roles = [];

    public bool $isVerified = false;

    public ?\DateTimeImmutable $createdAt = null;

    public ?\DateTimeImmutable $updatedAt = null;
}
