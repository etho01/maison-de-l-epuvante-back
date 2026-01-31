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
use App\Ecommerce\Entity\Product as ProductEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    shortName: 'Product',
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['product:read', 'product:list']],
        ),
        new Get(
            normalizationContext: ['groups' => ['product:read', 'product:detail']],
        ),
        new Post(
            security: "is_granted('ROLE_ADMIN')",
            denormalizationContext: ['groups' => ['product:write']],
        ),
        new Put(
            security: "is_granted('ROLE_ADMIN')",
            denormalizationContext: ['groups' => ['product:write']],
        ),
        new Patch(
            security: "is_granted('ROLE_ADMIN')",
            denormalizationContext: ['groups' => ['product:write']],
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN')",
        ),
    ],
    normalizationContext: ['groups' => ['product:read']],
    denormalizationContext: ['groups' => ['product:write']],
    paginationEnabled: true,
)]
#[ApiFilter(SearchFilter::class, properties: ['name' => 'partial', 'type' => 'exact', 'category.id' => 'exact'])]
#[ApiFilter(RangeFilter::class, properties: ['price'])]
#[ApiFilter(BooleanFilter::class, properties: ['active', 'exclusiveOnline'])]
class Product extends ProductEntity
{
}
