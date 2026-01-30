<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use App\Entity\Subscription as SubscriptionEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    shortName: 'Subscription',
    operations: [
        new GetCollection(
            security: "is_granted('ROLE_USER')",
            normalizationContext: ['groups' => ['subscription:read', 'subscription:list']],
        ),
        new Get(
            security: "is_granted('ROLE_ADMIN') or object.user == user",
            normalizationContext: ['groups' => ['subscription:read', 'subscription:detail']],
        ),
        new Post(
            uriTemplate: '/subscriptions/subscribe',
            security: "is_granted('ROLE_USER')",
            denormalizationContext: ['groups' => ['subscription:create']],
            name: 'api_subscription_subscribe',
        ),
        new Patch(
            uriTemplate: '/subscriptions/{id}/cancel',
            security: "is_granted('ROLE_USER') and object.user == user",
            name: 'api_subscription_cancel',
        ),
        new Patch(
            uriTemplate: '/subscriptions/{id}/renew',
            security: "is_granted('ROLE_USER') and object.user == user",
            denormalizationContext: ['groups' => ['subscription:renew']],
            name: 'api_subscription_renew',
        ),
    ],
    normalizationContext: ['groups' => ['subscription:read']],
    denormalizationContext: ['groups' => ['subscription:write']],
    paginationEnabled: true,
)]
class Subscription extends SubscriptionEntity
{
}
