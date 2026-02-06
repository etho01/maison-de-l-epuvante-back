<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

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
            controller: \App\Controller\CreateSubscriptionController::class,
            denormalizationContext: ['groups' => ['subscription:create']],
            name: 'api_subscription_subscribe',
            read: false
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
    formats: ['json' => ['application/json'], 'jsonld' => ['application/ld+json']],
    normalizationContext: ['groups' => ['subscription:read']],
    denormalizationContext: ['groups' => ['subscription:write']],
    paginationEnabled: true,
)]
class Subscription
{
    #[Groups(['subscription:read', 'subscription:list', 'subscription:detail'])]
    public ?int $id = null;

    #[Assert\NotBlank(message: 'L\'ID du plan est requis', groups: ['subscription:create'])]
    #[Assert\Positive(message: 'L\'ID du plan doit être positif', groups: ['subscription:create'])]
    #[Groups(['subscription:create'])]
    public ?int $planId = null;

    #[Groups(['subscription:read', 'subscription:detail'])]
    public ?int $userId = null;

    #[Assert\Choice(
        choices: ['active', 'cancelled', 'expired', 'pending'],
        message: 'Le statut doit être active, cancelled, expired ou pending',
        groups: ['subscription:create', 'subscription:write']
    )]
    #[Groups(['subscription:read', 'subscription:list', 'subscription:detail', 'subscription:create', 'subscription:write'])]
    public string $status = 'pending';

    #[Assert\Length(max: 255, groups: ['subscription:create'])]
    #[Groups(['subscription:read', 'subscription:detail', 'subscription:create'])]
    public ?string $stripeSubscriptionId = null;

    #[Groups(['subscription:read', 'subscription:detail'])]
    public ?\DateTimeInterface $startDate = null;

    #[Groups(['subscription:read', 'subscription:detail'])]
    public ?\DateTimeInterface $endDate = null;

    #[Groups(['subscription:read', 'subscription:detail', 'subscription:create'])]
    public bool $autoRenew = true;

    #[Groups(['subscription:read', 'subscription:detail'])]
    public ?\DateTimeImmutable $createdAt = null;

    #[Groups(['subscription:read', 'subscription:detail'])]
    public ?\DateTimeImmutable $updatedAt = null;
}
