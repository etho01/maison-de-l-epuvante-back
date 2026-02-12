<?php

namespace App\Ecommerce\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'Category',
    operations: [
        new GetCollection(
            controller: \App\Ecommerce\Controller\GetCategoriesController::class,
            normalizationContext: ['groups' => ['category:read', 'category:list', 'parent', 'parent:read']],
        ),
        new Get(
            controller: \App\Ecommerce\Controller\GetCategoryController::class,
            normalizationContext: ['groups' => ['category:read', 'category:detail']],
        ),
        new Post(
            security: "is_granted('ROLE_ADMIN')",
            controller: \App\Ecommerce\Controller\CreateCategoryController::class,
            denormalizationContext: ['groups' => ['category:write']],
        ),
        new Put(
            security: "is_granted('ROLE_ADMIN')",
            controller: \App\Ecommerce\Controller\UpdateCategoryController::class,
            denormalizationContext: ['groups' => ['category:write']],
        ),
        new Patch(
            security: "is_granted('ROLE_ADMIN')",
            controller: \App\Ecommerce\Controller\UpdateCategoryController::class,
            denormalizationContext: ['groups' => ['category:write']],
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN')",
            controller: \App\Ecommerce\Controller\DeleteCategoryController::class,
        ),
    ],
    formats: ['json' => ['application/json'], 'jsonld' => ['application/ld+json']],
    normalizationContext: ['groups' => ['category:read']],
    denormalizationContext: ['groups' => ['category:write']],
    paginationEnabled: true,
)]
class Category
{
    public ?int $id = null;

    #[Assert\NotBlank(message: 'Le nom de la catégorie est requis', groups: ['category:write'])]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Le nom doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères',
        groups: ['category:write']
    )]
    public ?string $name = null;

    #[Assert\Length(max: 2000, maxMessage: 'La description ne peut pas dépasser {{ limit }} caractères', groups: ['category:write'])]
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
    public ?string $slug = null;

    public ?int $parentId = null;

    public ?\DateTimeImmutable $createdAt = null;

    public ?\DateTimeImmutable $updatedAt = null;
}
