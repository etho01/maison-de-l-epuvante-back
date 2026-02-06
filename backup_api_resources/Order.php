<?php

namespace App\Ecommerce\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use App\Ecommerce\State\OrderProvider;
use App\Ecommerce\State\OrderProcessor;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

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
            controller: \App\Ecommerce\Controller\CreateOrderController::class,
            denormalizationContext: ['groups' => ['order:create']],
            name: 'api_order_checkout',
            read: false
        ),
        new Patch(
            security: "is_granted('ROLE_ADMIN')",
            denormalizationContext: ['groups' => ['order:update']],
        ),
    ],
    formats: ['json' => ['application/json'], 'jsonld' => ['application/ld+json']],
    normalizationContext: ['groups' => ['order:read']],
    denormalizationContext: ['groups' => ['order:write']],
    paginationEnabled: true,
    provider: OrderProvider::class,
    processor: OrderProcessor::class,
)]
class Order
{
    #[Groups(['order:read', 'order:list', 'order:detail'])]
    public ?int $id = null;

    #[Groups(['order:read', 'order:detail'])]
    public ?int $userId = null;

    #[Assert\NotBlank(message: 'Le statut est requis', groups: ['order:create', 'order:update'])]
    #[Assert\Choice(
        choices: ['pending', 'processing', 'completed', 'cancelled', 'refunded'],
        message: 'Le statut doit être pending, processing, completed, cancelled ou refunded',
        groups: ['order:create', 'order:update']
    )]
    #[Groups(['order:read', 'order:list', 'order:detail', 'order:create', 'order:update'])]
    public string $status = 'pending';

    #[Assert\NotBlank(message: 'L\'adresse de livraison est requise', groups: ['order:create'])]
    #[Assert\Length(max: 500, groups: ['order:create'])]
    #[Groups(['order:read', 'order:detail', 'order:create'])]
    public ?string $shippingAddress = null;

    #[Assert\NotBlank(message: 'L\'adresse de facturation est requise', groups: ['order:create'])]
    #[Assert\Length(max: 500, groups: ['order:create'])]
    #[Groups(['order:read', 'order:detail', 'order:create'])]
    public ?string $billingAddress = null;

    #[Assert\Length(max: 50, groups: ['order:create'])]
    #[Groups(['order:read', 'order:detail', 'order:create'])]
    public ?string $paymentMethod = null;

    #[Assert\Length(max: 255, groups: ['order:create'])]
    #[Groups(['order:read', 'order:detail', 'order:create'])]
    public ?string $paymentIntentId = null;

    #[Groups(['order:read', 'order:list', 'order:detail'])]
    public ?float $totalAmount = 0.0;

    #[Groups(['order:read', 'order:detail'])]
    public array $items = [];

    #[Groups(['order:read', 'order:detail'])]
    public ?\DateTimeImmutable $createdAt = null;

    #[Groups(['order:read', 'order:detail'])]
    public ?\DateTimeImmutable $updatedAt = null;
}
