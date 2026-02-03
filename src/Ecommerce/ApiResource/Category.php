<?php

namespace App\Ecommerce\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use App\Ecommerce\Entity\Category as CategoryEntity;
use App\Ecommerce\State\CategoryProvider;
use App\Ecommerce\State\CategoryProcessor;

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
            denormalizationContext: ['groups' => ['category:write']],
        ),
        new Put(
            security: "is_granted('ROLE_ADMIN')",
            denormalizationContext: ['groups' => ['category:write']],
        ),
        new Patch(
            security: "is_granted('ROLE_ADMIN')",
            denormalizationContext: ['groups' => ['category:write']],
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN')",
        ),
    ],
    normalizationContext: ['groups' => ['category:read']],
    denormalizationContext: ['groups' => ['category:write']],
    paginationEnabled: true,
    provider: CategoryProvider::class,
    processor: CategoryProcessor::class,
)]
class Category extends CategoryEntity
{
}
