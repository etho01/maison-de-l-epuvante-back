<?php

namespace App\Ecommerce\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'Order',
    operations: [
        new GetCollection(
            security: "is_granted('ROLE_USER')",
            controller: \App\Ecommerce\Controller\Order\GetOrdersController::class,
            normalizationContext: ['groups' => ['order:read', 'order:list']],
        ),
        new Get(
            security: "is_granted('ROLE_ADMIN') or object.user == user",
            controller: \App\Ecommerce\Controller\Order\GetOrderController::class,
            normalizationContext: ['groups' => ['order:read', 'order:detail']],
        ),
        new Get(
            uriTemplate: '/orders/payment-intent/{paymentIntentId}',
            security: "is_granted('ROLE_USER')",
            controller: \App\Ecommerce\Controller\Order\GetOrderByPaymentIntentController::class,
            normalizationContext: ['groups' => ['order:read', 'order:detail']],
            name: 'api_order_by_payment_intent',
        ),
        new Post(
            uriTemplate: '/orders/checkout',
            security: "is_granted('ROLE_USER')",
            controller: \App\Ecommerce\Controller\Order\CreateOrderController::class,
            input: \App\Ecommerce\Dto\OrderCheckoutInput::class,
            denormalizationContext: ['groups' => ['order:create']],
            name: 'api_order_checkout',
        ),
        new Patch(
            security: "is_granted('ROLE_ADMIN')",
            controller: \App\Ecommerce\Controller\Order\UpdateOrderController::class,
            denormalizationContext: ['groups' => ['order:update']],
        ),
    ],
    formats: ['json' => ['application/json'], 'jsonld' => ['application/ld+json']],
    normalizationContext: ['groups' => ['order:read']],
    denormalizationContext: ['groups' => ['order:write']],
    paginationEnabled: true,
)]
class Order
{
    public ?int $id = null;

    public ?int $userId = null;

    #[Assert\NotBlank(message: 'Le statut est requis', groups: ['order:create', 'order:update'])]
    #[Assert\Choice(
        choices: ['pending', 'processing', 'completed', 'cancelled', 'refunded'],
        message: 'Le statut doit être pending, processing, completed, cancelled ou refunded',
        groups: ['order:create', 'order:update']
    )]
    public string $status = 'pending';

    #[Assert\NotBlank(message: 'L\'adresse de facturation est requise', groups: ['order:create'])]
    #[Assert\Length(max: 500, groups: ['order:create'])]
    public ?string $billingAddress = null;

    #[Assert\Length(max: 50, groups: ['order:create'])]
    public ?string $paymentMethod = null;

    #[Assert\Length(max: 255, groups: ['order:create'])]
    public ?string $paymentIntentId = null;

    public ?float $totalAmount = 0.0;

    public array $items = [];

    public ?\DateTimeImmutable $createdAt = null;

    public ?\DateTimeImmutable $updatedAt = null;
}
