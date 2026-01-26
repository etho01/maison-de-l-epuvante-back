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
            security: "is_granted('ROLE_ADMIN')"
        ),
        new Get(
            security: "is_granted('ROLE_ADMIN') or object.id == user?.getId()"
        ),
        new Post(
            security: "is_granted('PUBLIC_ACCESS')",
            validationContext: ['groups' => ['Default', 'user:create']],
        ),
        new Put(
            security: "is_granted('ROLE_ADMIN') or object.id == user?.getId()"
        ),
        new Patch(
            security: "is_granted('ROLE_ADMIN') or object.id == user?.getId()"
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
            security: "is_granted('ROLE_USER')"
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
    normalizationContext: ['groups' => ['user:read']],
    denormalizationContext: ['groups' => ['user:write']],
    provider: UserProvider::class,
    processor: UserStateProcessor::class,
)]
class User
{
    #[Groups(['user:read'])]
    public ?int $id = null;

    #[Assert\NotBlank(groups: ['user:create'])]
    #[Assert\Email]
    #[Groups(['user:read', 'user:write'])]
    public ?string $email = null;

    #[Groups(['user:read'])]
    public array $roles = [];

    #[Assert\NotBlank(groups: ['user:create'])]
    #[Assert\Length(min: 8, groups: ['user:create', 'user:password'])]
    #[Groups(['user:write'])]
    public ?string $plainPassword = null;

    #[Groups(['user:read', 'user:write'])]
    public ?string $firstName = null;

    #[Groups(['user:read', 'user:write'])]
    public ?string $lastName = null;

    #[Groups(['user:read'])]
    public bool $isVerified = false;

    #[Groups(['user:read'])]
    public ?\DateTimeImmutable $createdAt = null;

    #[Groups(['user:read'])]
    public ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }
}
