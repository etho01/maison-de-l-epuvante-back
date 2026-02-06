<?php

namespace App\Ecommerce\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use App\Ecommerce\State\CategoryProvider;
use App\Ecommerce\State\CategoryProcessor;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'Category',
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['category:read', 'category:list']],
        ),
        new Get(
            normalizationContext: ['groups' => ['category:read', 'category:detail']],
        ),
        new Post(
            security: "is_granted('ROLE_ADMIN')",
            controller: \App\Ecommerce\Controller\CreateCategoryController::class,
            denormalizationContext: ['groups' => ['category:write']],
            read: false
        ),
        new Put(
            security: "is_granted('ROLE_ADMIN')",
            controller: \App\Ecommerce\Controller\UpdateCategoryController::class,
            denormalizationContext: ['groups' => ['category:write']],
            read: false
        ),
        new Patch(
            security: "is_granted('ROLE_ADMIN')",
            controller: \App\Ecommerce\Controller\UpdateCategoryController::class,
            denormalizationContext: ['groups' => ['category:write']],
            read: false
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN')",
        ),
    ],
    formats: ['json' => ['application/json'], 'jsonld' => ['application/ld+json']],
    normalizationContext: ['groups' => ['category:read']],
    denormalizationContext: ['groups' => ['category:write']],
    paginationEnabled: true,
    provider: CategoryProvider::class,
    processor: CategoryProcessor::class,
)]
class Category
{
    #[Groups(['category:read', 'category:list', 'category:detail', 'product:read'])]
    public ?int $id = null;

    #[Assert\NotBlank(message: 'Le nom de la catégorie est requis', groups: ['category:write'])]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Le nom doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères',
        groups: ['category:write']
    )]
    #[Groups(['category:read', 'category:list', 'category:detail', 'category:write', 'product:read'])]
    public ?string $name = null;

    #[Assert\Length(max: 2000, maxMessage: 'La description ne peut pas dépasser {{ limit }} caractères', groups: ['category:write'])]
    #[Groups(['category:read', 'category:detail', 'category:write'])]
    public ?string $description = null;

    #[Assert\NotBlank(message: 'Le slug est requis', groups: ['category:write'])]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Le slug doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le slug ne peut pas dépasser {{ limit }} caractères',
        groups: ['category:write']
    )]
    #[Assert\Regex(
        pattern: '/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
        message: 'Le slug doit être en minuscules avec des tirets uniquement',
        groups: ['category:write']
    )]
    #[Groups(['category:read', 'category:list', 'category:detail', 'category:write'])]
    public ?string $slug = null;

    #[Groups(['category:write'])]
    public ?int $parentId = null;

    #[Groups(['category:read', 'category:detail'])]
    public ?\DateTimeImmutable $createdAt = null;

    #[Groups(['category:read', 'category:detail'])]
    public ?\DateTimeImmutable $updatedAt = null;
}
