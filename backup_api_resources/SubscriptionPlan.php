<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use App\State\SubscriptionPlanProvider;
use App\State\SubscriptionPlanProcessor;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

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
            controller: \App\Controller\CreateSubscriptionPlanController::class,
            denormalizationContext: ['groups' => ['subscription_plan:write']],
            read: false
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
    formats: ['json' => ['application/json'], 'jsonld' => ['application/ld+json']],
    normalizationContext: ['groups' => ['subscription_plan:read']],
    denormalizationContext: ['groups' => ['subscription_plan:write']],
    paginationEnabled: false,
    provider: SubscriptionPlanProvider::class,
    processor: SubscriptionPlanProcessor::class,
)]
class SubscriptionPlan
{
    #[Groups(['subscription_plan:read', 'subscription_plan:list', 'subscription_plan:detail'])]
    public ?int $id = null;

    #[Assert\NotBlank(message: 'Le nom du plan est requis', groups: ['subscription_plan:write'])]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Le nom doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères',
        groups: ['subscription_plan:write']
    )]
    #[Groups(['subscription_plan:read', 'subscription_plan:list', 'subscription_plan:detail', 'subscription_plan:write'])]
    public ?string $name = null;

    #[Assert\Length(max: 2000, groups: ['subscription_plan:write'])]
    #[Groups(['subscription_plan:read', 'subscription_plan:detail', 'subscription_plan:write'])]
    public ?string $description = null;

    #[Assert\NotBlank(message: 'Le prix est requis', groups: ['subscription_plan:write'])]
    #[Assert\Positive(message: 'Le prix doit être positif', groups: ['subscription_plan:write'])]
    #[Assert\Range(min: 0.01, max: 99999.99, groups: ['subscription_plan:write'])]
    #[Groups(['subscription_plan:read', 'subscription_plan:list', 'subscription_plan:detail', 'subscription_plan:write'])]
    public ?float $price = null;

    #[Assert\NotBlank(message: 'La durée est requise', groups: ['subscription_plan:write'])]
    #[Assert\Positive(message: 'La durée doit être positive', groups: ['subscription_plan:write'])]
    #[Assert\Range(min: 1, max: 365, groups: ['subscription_plan:write'])]
    #[Groups(['subscription_plan:read', 'subscription_plan:list', 'subscription_plan:detail', 'subscription_plan:write'])]
    public ?int $duration = null;

    #[Assert\NotBlank(message: 'L\'unité de durée est requise', groups: ['subscription_plan:write'])]
    #[Assert\Choice(
        choices: ['day', 'week', 'month', 'year'],
        message: 'L\'unité doit être day, week, month ou year',
        groups: ['subscription_plan:write']
    )]
    #[Groups(['subscription_plan:read', 'subscription_plan:list', 'subscription_plan:detail', 'subscription_plan:write'])]
    public string $durationUnit = 'month';

    #[Groups(['subscription_plan:read', 'subscription_plan:detail', 'subscription_plan:write'])]
    public bool $active = true;

    #[Groups(['subscription_plan:read', 'subscription_plan:detail', 'subscription_plan:write'])]
    public ?array $features = [];

    #[Groups(['subscription_plan:read', 'subscription_plan:detail'])]
    public ?\DateTimeImmutable $createdAt = null;

    #[Groups(['subscription_plan:read', 'subscription_plan:detail'])]
    public ?\DateTimeImmutable $updatedAt = null;
}
