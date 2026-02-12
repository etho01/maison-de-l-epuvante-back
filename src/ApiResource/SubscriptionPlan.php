<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'SubscriptionPlan',
    operations: [
        new GetCollection(
            controller: \App\Controller\GetSubscriptionPlansController::class,
            normalizationContext: ['groups' => ['subscription_plan:read', 'subscription_plan:list']],
        ),
        new Get(
            controller: \App\Controller\GetSubscriptionPlanController::class,
            normalizationContext: ['groups' => ['subscription_plan:read', 'subscription_plan:detail']],
        ),
        new Post(
            security: "is_granted('ROLE_ADMIN')",
            controller: \App\Controller\CreateSubscriptionPlanController::class,
            denormalizationContext: ['groups' => ['subscription_plan:write']],
        ),
        new Put(
            security: "is_granted('ROLE_ADMIN')",
            controller: \App\Controller\UpdateSubscriptionPlanController::class,
            denormalizationContext: ['groups' => ['subscription_plan:write']],
        ),
        new Patch(
            security: "is_granted('ROLE_ADMIN')",
            controller: \App\Controller\UpdateSubscriptionPlanController::class,
            denormalizationContext: ['groups' => ['subscription_plan:write']],
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN')",
            controller: \App\Controller\DeleteSubscriptionPlanController::class,
        ),
    ],
    formats: ['json' => ['application/json'], 'jsonld' => ['application/ld+json']],
    normalizationContext: ['groups' => ['subscription_plan:read']],
    denormalizationContext: ['groups' => ['subscription_plan:write']],
    paginationEnabled: false,
)]
class SubscriptionPlan
{
    public ?int $id = null;

    #[Assert\NotBlank(message: 'Le nom du plan est requis', groups: ['subscription_plan:write'])]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Le nom doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères',
        groups: ['subscription_plan:write']
    )]
    public ?string $name = null;

    #[Assert\Length(max: 2000, groups: ['subscription_plan:write'])]
    public ?string $description = null;

    #[Assert\NotBlank(message: 'Le prix est requis', groups: ['subscription_plan:write'])]
    #[Assert\Positive(message: 'Le prix doit être positif', groups: ['subscription_plan:write'])]
    #[Assert\Range(min: 0.01, max: 99999.99, groups: ['subscription_plan:write'])]
    public ?float $price = null;

    #[Assert\NotBlank(message: 'La durée est requise', groups: ['subscription_plan:write'])]
    #[Assert\Positive(message: 'La durée doit être positive', groups: ['subscription_plan:write'])]
    #[Assert\Range(min: 1, max: 120, groups: ['subscription_plan:write'])]
    public ?int $durationInMonths = null;

    #[Assert\NotBlank(message: 'L\'unité de durée est requise', groups: ['subscription_plan:write'])]
    #[Assert\Choice(
        choices: ['day', 'week', 'month', 'year'],
        message: 'L\'unité doit être day, week, month ou year',
        groups: ['subscription_plan:write']
    )]
    public string $billingInterval = 'monthly';

    #[Assert\Choice(
        choices: ['paper', 'digital', 'both'],
        message: 'Le format doit être paper, digital ou both',
        groups: ['subscription_plan:write']
    )]
    public ?string $format = 'both';

    public bool $active = true;

    public ?array $features = [];

    public ?\DateTimeImmutable $createdAt = null;

    public ?\DateTimeImmutable $updatedAt = null;
}
