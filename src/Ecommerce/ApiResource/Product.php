<?php

namespace App\Ecommerce\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'Product',
    operations: [
        new GetCollection(
            controller: \App\Ecommerce\Controller\GetProductsController::class,
            normalizationContext: ['groups' => ['product:read', 'product:list', 'category', 'category:read']],
        ),
        new Get(
            uriTemplate: '/products/{id}',
            controller: \App\Ecommerce\Controller\GetProductController::class,
            normalizationContext: ['groups' => ['product:read', 'product:detail']],
        ),
        new Get(
            uriTemplate: '/products/slug/{slug}',
            controller: \App\Ecommerce\Controller\GetProductBySlugController::class,
            normalizationContext: ['groups' => ['product:read', 'product:detail']],
        ),
        new Post(
            security: "is_granted('ROLE_ADMIN')",
            controller: \App\Ecommerce\Controller\CreateProductController::class,
            denormalizationContext: ['groups' => ['product:write']],
        ),
        new Put(
            security: "is_granted('ROLE_ADMIN')",
            controller: \App\Ecommerce\Controller\UpdateProductController::class,
            denormalizationContext: ['groups' => ['product:write']],
        ),
        new Patch(
            security: "is_granted('ROLE_ADMIN')",
            controller: \App\Ecommerce\Controller\UpdateProductController::class,
            denormalizationContext: ['groups' => ['product:write']],
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN')",
            controller: \App\Ecommerce\Controller\DeleteProductController::class,
        ),
    ],
    formats: ['json' => ['application/json'], 'jsonld' => ['application/ld+json']],
    normalizationContext: ['groups' => ['product:read']],
    denormalizationContext: ['groups' => ['product:write']],
    paginationEnabled: true,
)]
#[ApiFilter(SearchFilter::class, properties: ['name' => 'partial', 'type' => 'exact', 'category.id' => 'exact'])]
#[ApiFilter(RangeFilter::class, properties: ['price'])]
#[ApiFilter(BooleanFilter::class, properties: ['active', 'exclusiveOnline'])]
class Product
{
    public ?int $id = null;

    #[Assert\NotBlank(message: 'Le nom du produit est requis', groups: ['product:write'])]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Le nom doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères',
        groups: ['product:write']
    )]
    public ?string $name = null;

    #[Assert\Length(max: 5000, maxMessage: 'La description ne peut pas dépasser {{ limit }} caractères', groups: ['product:write'])]
    public ?string $description = null;

    #[Assert\NotBlank(message: 'Le slug est requis', groups: ['product:write'])]
    #[Assert\Regex(
        pattern: '/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
        message: 'Le slug doit être en minuscules avec des tirets uniquement',
        groups: ['product:write']
    )]
    public ?string $slug = null;

    #[Assert\NotBlank(message: 'Le prix est requis', groups: ['product:write'])]
    #[Assert\Positive(message: 'Le prix doit être positif', groups: ['product:write'])]
    #[Assert\Range(
        min: 0.01,
        max: 999999.99,
        notInRangeMessage: 'Le prix doit être entre {{ min }} et {{ max }}',
        groups: ['product:write']
    )]
    public ?float $price = null;

    #[Assert\NotNull(message: 'Le stock est requis', groups: ['product:write'])]
    #[Assert\PositiveOrZero(message: 'Le stock ne peut pas être négatif', groups: ['product:write'])]
    #[Assert\Range(
        min: 0,
        max: 100000,
        notInRangeMessage: 'Le stock doit être entre {{ min }} et {{ max }}',
        groups: ['product:write']
    )]
    public int $stock = 0;

    #[Assert\NotBlank(message: 'Le type est requis', groups: ['product:write'])]
    #[Assert\Choice(
        choices: ['physical', 'digital', 'subscription'],
        message: 'Le type doit être physical, digital ou subscription',
        groups: ['product:write']
    )]
    public string $type = 'physical';

    #[Assert\Length(max: 255, groups: ['product:write'])]
    public ?string $sku = null;

    public ?array $images = [];

    public bool $active = true;

    public bool $exclusiveOnline = false;

    #[Assert\Positive(message: 'L\'ID de catégorie doit être positif', groups: ['product:write'])]
    public ?int $categoryId = null;

    #[Assert\PositiveOrZero(message: 'Le poids ne peut pas être négatif', groups: ['product:write'])]
    public ?float $weight = null;

    public ?array $metadata = [];

    public ?\DateTimeImmutable $createdAt = null;

    public ?\DateTimeImmutable $updatedAt = null;
}
