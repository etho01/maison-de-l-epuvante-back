<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use App\Entity\SubscriptionPlan as SubscriptionPlanEntity;
use App\State\SubscriptionPlanProvider;
use App\State\SubscriptionPlanProcessor;

#[ApiResource(
    shortName: 'SubscriptionPlan',
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['subscription_plan:read', 'subscription_plan:list']],
        ),
        new Get(
            normalizationContext: ['groups' => ['subscription_plan:read', 'subscription_plan:detail']],
        ),
        new Post(
            security: "is_granted('ROLE_ADMIN')",
            denormalizationContext: ['groups' => ['subscription_plan:write']],
        ),
        new Put(
            security: "is_granted('ROLE_ADMIN')",
            denormalizationContext: ['groups' => ['subscription_plan:write']],
        ),
        new Patch(
            security: "is_granted('ROLE_ADMIN')",
            denormalizationContext: ['groups' => ['subscription_plan:write']],
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN')",
        ),
    ],
    normalizationContext: ['groups' => ['subscription_plan:read']],
    denormalizationContext: ['groups' => ['subscription_plan:write']],
    paginationEnabled: false,
    provider: SubscriptionPlanProvider::class,
    processor: SubscriptionPlanProcessor::class,
)]
class SubscriptionPlan extends SubscriptionPlanEntity
{
}
