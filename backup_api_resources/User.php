<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use App\State\UserProvider;
use App\State\UserStateProcessor;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        // CRUD Operations
        new GetCollection(
            security: "is_granted('ROLE_ADMIN')",
            normalizationContext: ['groups' => ['user:read', 'user:list']]
        ),
        new Get(
            security: "is_granted('ROLE_ADMIN') or object.id == user?.getId()",
            normalizationContext: ['groups' => ['user:read', 'user:detail']]
        ),
        new Post(
            security: "is_granted('PUBLIC_ACCESS')",
            controller: \App\Controller\CreateUserController::class,
            validationContext: ['groups' => ['Default', 'user:create']],
            denormalizationContext: ['groups' => ['user:write']],
            read: false
        ),
        new Put(
            security: "is_granted('ROLE_ADMIN') or object.id == user?.getId()",
            controller: \App\Controller\UpdateUserController::class,
            denormalizationContext: ['groups' => ['user:write']],
            read: false
        ),
        new Patch(
            security: "is_granted('ROLE_ADMIN') or object.id == user?.getId()",
            controller: \App\Controller\UpdateUserController::class,
            denormalizationContext: ['groups' => ['user:write']],
            read: false
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN')"
        ),
        
        // Authentication Operations
        new Post(
            uriTemplate: '/login',
            controller: \App\Controller\AuthController::class . '::login',
            name: 'api_login',
            security: "is_granted('PUBLIC_ACCESS')"
        ),
        new Get(
            uriTemplate: '/me',
            controller: \App\Controller\AuthController::class . '::getCurrentUser',
            name: 'api_current_user',
            security: "is_granted('ROLE_USER')",
            read: false,
            deserialize: false
        ),
        new Patch(
            uriTemplate: '/me',
            controller: \App\Controller\AuthController::class . '::updateCurrentUser',
            name: 'api_update_current_user',
            security: "is_granted('ROLE_USER')",
            read: false,
            deserialize: false
        ),
        
        // Password Management Operations
        new Post(
            uriTemplate: '/change-password',
            controller: \App\Controller\PasswordController::class . '::changePassword',
            name: 'api_change_password',
            security: "is_granted('ROLE_USER')"
        ),
        new Post(
            uriTemplate: '/reset-password-request',
            controller: \App\Controller\PasswordController::class . '::requestResetPassword',
            name: 'api_reset_password_request',
            security: "is_granted('PUBLIC_ACCESS')"
        ),
        new Post(
            uriTemplate: '/reset-password-confirm',
            controller: \App\Controller\PasswordController::class . '::confirmResetPassword',
            name: 'api_reset_password_confirm',
            security: "is_granted('PUBLIC_ACCESS')"
        ),
        
        // Email Verification Operations
        new Get(
            uriTemplate: '/verify/email',
            controller: \App\Controller\VerifyEmailController::class . '::verifyUserEmail',
            name: 'api_verify_email',
            security: "is_granted('PUBLIC_ACCESS')"
        ),
        new Post(
            uriTemplate: '/verify/resend',
            controller: \App\Controller\VerifyEmailController::class . '::resendVerificationEmail',
            name: 'api_resend_verify_email',
            security: "is_granted('ROLE_USER')"
        ),
    ],
    formats: ['json' => ['application/json'], 'jsonld' => ['application/ld+json']],
    normalizationContext: ['groups' => ['user:read']],
    denormalizationContext: ['groups' => ['user:write']],
    provider: UserProvider::class,
    processor: UserStateProcessor::class,
)]
class User
{
    #[Groups(['user:read', 'user:list', 'user:detail'])]
    public ?int $id = null;

    #[Assert\NotBlank(message: 'L\'email est requis', groups: ['user:create', 'user:write'])]
    #[Assert\Email(message: 'L\'email {{ value }} n\'est pas valide')]
    #[Assert\Length(max: 180, maxMessage: 'L\'email ne peut pas dépasser {{ limit }} caractères')]
    #[Groups(['user:read', 'user:write', 'user:list', 'user:detail'])]
    public ?string $email = null;

    #[Groups(['user:read', 'user:list', 'user:detail'])]
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
    #[Groups(['user:write'])]
    public ?string $password = null;

    #[Assert\Length(max: 255)]
    #[Groups(['user:read', 'user:write', 'user:detail'])]
    public ?string $firstName = null;

    #[Assert\Length(max: 255)]
    #[Groups(['user:read', 'user:write', 'user:detail'])]
    public ?string $lastName = null;

    #[Groups(['user:read', 'user:detail'])]
    public bool $isVerified = false;

    #[Groups(['user:read', 'user:detail'])]
    public ?\DateTimeImmutable $createdAt = null;

    #[Groups(['user:read', 'user:detail'])]
    public ?\DateTimeImmutable $updatedAt = null;
}
