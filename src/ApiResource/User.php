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
    operations: [
        // CRUD Operations
        new GetCollection(
            security: "is_granted('ROLE_ADMIN')",
            controller: \App\Controller\User\GetUsersController::class,
            normalizationContext: ['groups' => ['user:read', 'user:list']]
        ),
        new Get(
            security: "is_granted('ROLE_ADMIN') or object.id == user?.getId()",
            controller: \App\Controller\User\GetUserController::class,
            normalizationContext: ['groups' => ['user:read', 'user:detail']]
        ),
        new Post(
            security: "is_granted('PUBLIC_ACCESS')",
            controller: \App\Controller\User\CreateUserController::class,
            validationContext: ['groups' => ['Default', 'user:create']],
            denormalizationContext: ['groups' => ['user:write']],
        ),
        new Put(
            security: "is_granted('ROLE_ADMIN') or object.id == user?.getId()",
            controller: \App\Controller\User\UpdateUserController::class,
            denormalizationContext: ['groups' => ['user:write']],
        ),
        new Patch(
            security: "is_granted('ROLE_ADMIN') or object.id == user?.getId()",
            controller: \App\Controller\User\UpdateUserController::class,
            denormalizationContext: ['groups' => ['user:write']],
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN')",
            controller: \App\Controller\User\DeleteUserController::class,
        ),
        
        // Authentication Operations
        new Post(
            uriTemplate: '/login',
            controller: \App\Controller\Auth\AuthController::class . '::login',
            name: 'api_login',
            security: "is_granted('PUBLIC_ACCESS')"
        ),
        new Get(
            uriTemplate: '/me',
            controller: \App\Controller\Auth\AuthController::class . '::getCurrentUser',
            name: 'api_current_user',
            security: "is_granted('ROLE_USER')",
        ),
        new Patch(
            uriTemplate: '/me',
            controller: \App\Controller\Auth\AuthController::class . '::updateCurrentUser',
            name: 'api_update_current_user',
            security: "is_granted('ROLE_USER')",
        ),
        
        // Password Management Operations
        new Post(
            uriTemplate: '/change-password',
            controller: \App\Controller\Auth\PasswordController::class . '::changePassword',
            name: 'api_change_password',
            security: "is_granted('ROLE_USER')"
        ),
        new Post(
            uriTemplate: '/reset-password-request',
            controller: \App\Controller\Auth\PasswordController::class . '::requestResetPassword',
            name: 'api_reset_password_request',
            security: "is_granted('PUBLIC_ACCESS')"
        ),
        new Post(
            uriTemplate: '/reset-password-confirm',
            controller: \App\Controller\Auth\PasswordController::class . '::confirmResetPassword',
            name: 'api_reset_password_confirm',
            security: "is_granted('PUBLIC_ACCESS')"
        ),
        
        // Email Verification Operations
        new Get(
            uriTemplate: '/verify/email',
            controller: \App\Controller\Auth\VerifyEmailController::class . '::verifyUserEmail',
            name: 'api_verify_email',
            security: "is_granted('PUBLIC_ACCESS')"
        ),
        new Post(
            uriTemplate: '/verify/resend',
            controller: \App\Controller\Auth\VerifyEmailController::class . '::resendVerificationEmail',
            name: 'api_resend_verify_email',
            security: "is_granted('ROLE_USER')"
        ),
    ],
    formats: ['json' => ['application/json'], 'jsonld' => ['application/ld+json']],
    normalizationContext: ['groups' => ['user:read']],
    denormalizationContext: ['groups' => ['user:write']],
)]
class User
{
    public ?int $id = null;

    #[Assert\NotBlank(message: 'L\'email est requis', groups: ['user:create', 'user:write'])]
    #[Assert\Email(message: 'L\'email {{ value }} n\'est pas valide')]
    #[Assert\Length(max: 180, maxMessage: 'L\'email ne peut pas dépasser {{ limit }} caractères')]
    public ?string $email = null;

    public array $roles = ['ROLE_USER'];

    #[Assert\NotBlank(message: 'Le mot de passe est requis', groups: ['user:create'])]
    #[Assert\Length(
        min: 8,
        max: 255,
        minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le mot de passe ne peut pas dépasser {{ limit }} caractères',
        groups: ['user:create', 'user:password']
    )]
    #[Assert\Regex(
        pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/',
        message: 'Le mot de passe doit contenir au moins une majuscule, une minuscule et un chiffre',
        groups: ['user:create', 'user:password']
    )]
    public ?string $password = null;

    #[Assert\Length(max: 255)]
    public ?string $firstName = null;

    #[Assert\Length(max: 255)]
    public ?string $lastName = null;

    public bool $isVerified = false;

    public ?\DateTimeImmutable $createdAt = null;

    public ?\DateTimeImmutable $updatedAt = null;
}
