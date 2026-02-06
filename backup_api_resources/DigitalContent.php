<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'DigitalContent',
    operations: [
        new GetCollection(
            security: "is_granted('ROLE_USER')",
            normalizationContext: ['groups' => ['digital_content:read', 'digital_content:list']],
        ),
        new Get(
            security: "is_granted('ROLE_USER')",
            normalizationContext: ['groups' => ['digital_content:read', 'digital_content:detail']],
        ),
        new Get(
            uriTemplate: '/digital-contents/{id}/download',
            security: "is_granted('ROLE_USER')",
            name: 'api_digital_content_download',
        ),
        new Post(
            security: "is_granted('ROLE_ADMIN')",
            controller: \App\Controller\CreateDigitalContentController::class,
            denormalizationContext: ['groups' => ['digital_content:write']],
            name: 'api_digital_content_create',
            read: false
        ),
    ],
    formats: ['json' => ['application/json'], 'jsonld' => ['application/ld+json']],
    normalizationContext: ['groups' => ['digital_content:read']],
    denormalizationContext: ['groups' => ['digital_content:write']],
    paginationEnabled: true,
)]
class DigitalContent
{
    #[Groups(['digital_content:read', 'digital_content:list', 'digital_content:detail'])]
    public ?int $id = null;

    #[Assert\NotBlank(message: 'Le nom est requis', groups: ['digital_content:write'])]
    #[Assert\Length(min: 3, max: 255, groups: ['digital_content:write'])]
    #[Groups(['digital_content:read', 'digital_content:list', 'digital_content:detail', 'digital_content:write'])]
    public ?string $name = null;

    #[Assert\Length(max: 2000, groups: ['digital_content:write'])]
    #[Groups(['digital_content:read', 'digital_content:detail', 'digital_content:write'])]
    public ?string $description = null;

    #[Assert\NotBlank(message: 'Le chemin du fichier est requis', groups: ['digital_content:write'])]
    #[Assert\Length(max: 500, groups: ['digital_content:write'])]
    #[Groups(['digital_content:read', 'digital_content:detail', 'digital_content:write'])]
    public ?string $filePath = null;

    #[Assert\NotBlank(message: 'Le type de contenu est requis', groups: ['digital_content:write'])]
    #[Assert\Choice(
        choices: ['video', 'audio', 'document', 'image', 'archive'],
        message: 'Le type doit être video, audio, document, image ou archive',
        groups: ['digital_content:write']
    )]
    #[Groups(['digital_content:read', 'digital_content:list', 'digital_content:detail', 'digital_content:write'])]
    public ?string $contentType = null;

    #[Assert\PositiveOrZero(groups: ['digital_content:write'])]
    #[Groups(['digital_content:read', 'digital_content:detail', 'digital_content:write'])]
    public ?int $fileSize = null;

    #[Groups(['digital_content:write'])]
    public ?int $productId = null;

    #[Groups(['digital_content:write'])]
    public ?int $subscriptionPlanId = null;

    #[Groups(['digital_content:read', 'digital_content:detail', 'digital_content:write'])]
    public bool $requiresSubscription = false;

    #[Groups(['digital_content:read', 'digital_content:detail'])]
    public ?\DateTimeImmutable $createdAt = null;

    #[Groups(['digital_content:read', 'digital_content:detail'])]
    public ?\DateTimeImmutable $updatedAt = null;
}
