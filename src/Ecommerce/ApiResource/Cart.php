<?php

namespace App\Ecommerce\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use App\Ecommerce\Entity\Cart as CartEntity;
use App\Ecommerce\State\CartProvider;
use App\Ecommerce\State\CartProcessor;

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
    normalizationContext: ['groups' => ['cart:read']],
    denormalizationContext: ['groups' => ['cart:write']],
    provider: CartProvider::class,
    processor: CartProcessor::class,
)]
class Cart extends CartEntity
{
}
