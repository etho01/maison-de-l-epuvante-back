<?php

namespace App\Ecommerce\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use App\Ecommerce\Entity\Order as OrderEntity;
use App\Ecommerce\State\OrderProvider;
use App\Ecommerce\State\OrderProcessor;

#[ApiResource(
    shortName: 'Order',
    operations: [
        new GetCollection(
            security: "is_granted('ROLE_USER')",
            normalizationContext: ['groups' => ['order:read', 'order:list']],
        ),
        new Get(
            security: "is_granted('ROLE_ADMIN') or object.user == user",
            normalizationContext: ['groups' => ['order:read', 'order:detail']],
        ),
        new Post(
            uriTemplate: '/orders/checkout',
            security: "is_granted('ROLE_USER')",
            denormalizationContext: ['groups' => ['order:create']],
            name: 'api_order_checkout',
        ),
        new Patch(
            security: "is_granted('ROLE_ADMIN')",
            denormalizationContext: ['groups' => ['order:update']],
        ),
    ],
    normalizationContext: ['groups' => ['order:read']],
    denormalizationContext: ['groups' => ['order:write']],
    paginationEnabled: true,
    provider: OrderProvider::class,
    processor: OrderProcessor::class,
)]
class Order extends OrderEntity
{
}
