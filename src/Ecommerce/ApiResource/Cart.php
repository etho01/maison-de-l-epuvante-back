<?php

namespace App\Ecommerce\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'Cart',
    operations: [
        new Get(
            uriTemplate: '/cart/me',
            security: "is_granted('ROLE_USER')",
            normalizationContext: ['groups' => ['cart:read']],
        ),
        new Post(
            uriTemplate: '/cart/items',
            security: "is_granted('ROLE_USER')",
            denormalizationContext: ['groups' => ['cart:add']],
        ),
        new Patch(
            uriTemplate: '/cart/items/{itemId}',
            security: "is_granted('ROLE_USER')",
            denormalizationContext: ['groups' => ['cart:update']],
        ),
        new Delete(
            uriTemplate: '/cart/items/{itemId}',
            security: "is_granted('ROLE_USER')",
        ),
        new Delete(
            uriTemplate: '/cart/clear',
            security: "is_granted('ROLE_USER')",
        ),
    ],
    formats: ['json' => ['application/json'], 'jsonld' => ['application/ld+json']],
    normalizationContext: ['groups' => ['cart:read']],
    denormalizationContext: ['groups' => ['cart:write']],
)]
class Cart
{
    public ?int $id = null;

    public ?int $userId = null;

    #[Assert\NotBlank(message: 'L\'ID du produit est requis', groups: ['cart:add'])]
    #[Assert\Positive(message: 'L\'ID du produit doit être positif', groups: ['cart:add'])]
    public ?int $productId = null;

    #[Assert\NotBlank(message: 'La quantité est requise', groups: ['cart:add', 'cart:update'])]
    #[Assert\Positive(message: 'La quantité doit être positive', groups: ['cart:add', 'cart:update'])]
    #[Assert\Range(
        min: 1,
        max: 1000,
        notInRangeMessage: 'La quantité doit être entre {{ min }} et {{ max }}',
        groups: ['cart:add', 'cart:update']
    )]
    public int $quantity = 1;

    public array $items = [];

    public ?float $totalAmount = 0.0;

    public ?\DateTimeImmutable $createdAt = null;

    public ?\DateTimeImmutable $updatedAt = null;
}
