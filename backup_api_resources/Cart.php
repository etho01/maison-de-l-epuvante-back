<?php

namespace App\Ecommerce\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use App\Ecommerce\State\CartProvider;
use App\Ecommerce\State\CartProcessor;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'Cart',
    operations: [
        new Get(
            uriTemplate: '/cart/me',
            security: "is_granted('ROLE_USER')",
            normalizationContext: ['groups' => ['cart:read', 'cart:detail']],
            name: 'api_cart_me',
        ),
        new Post(
            uriTemplate: '/cart/items',
            security: "is_granted('ROLE_USER')",
            denormalizationContext: ['groups' => ['cart:write']],
            name: 'api_cart_add_item',
        ),
        new Patch(
            uriTemplate: '/cart/items/{itemId}',
            security: "is_granted('ROLE_USER')",
            denormalizationContext: ['groups' => ['cart:write']],
            name: 'api_cart_update_item',
        ),
        new Delete(
            uriTemplate: '/cart/items/{itemId}',
            security: "is_granted('ROLE_USER')",
            name: 'api_cart_remove_item',
        ),
        new Delete(
            uriTemplate: '/cart/clear',
            security: "is_granted('ROLE_USER')",
            name: 'api_cart_clear',
        ),
    ],
    formats: ['json' => ['application/json'], 'jsonld' => ['application/ld+json']],
    normalizationContext: ['groups' => ['cart:read']],
    denormalizationContext: ['groups' => ['cart:write']],
    provider: CartProvider::class,
    processor: CartProcessor::class,
)]
class Cart
{
    #[Groups(['cart:read', 'cart:detail'])]
    public ?int $id = null;

    #[Groups(['cart:read', 'cart:detail'])]
    public ?int $userId = null;

    #[Groups(['cart:read', 'cart:detail'])]
    public ?array $items = [];

    #[Groups(['cart:read', 'cart:detail'])]
    public ?float $totalAmount = 0.0;

    #[Assert\NotBlank(message: 'L\'ID du produit est requis', groups: ['cart:write'])]
    #[Assert\Positive(message: 'L\'ID du produit doit être positif', groups: ['cart:write'])]
    #[Groups(['cart:write'])]
    public ?int $productId = null;

    #[Assert\Positive(message: 'La quantité doit être positive', groups: ['cart:write'])]
    #[Assert\Range(min: 1, max: 1000, groups: ['cart:write'])]
    #[Groups(['cart:write'])]
    public int $quantity = 1;

    #[Groups(['cart:read', 'cart:detail'])]
    public ?\DateTimeImmutable $createdAt = null;

    #[Groups(['cart:read', 'cart:detail'])]
    public ?\DateTimeImmutable $updatedAt = null;
}
