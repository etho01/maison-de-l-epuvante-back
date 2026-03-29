<?php

namespace App\Ecommerce\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Ecommerce\Enum\DeliveryStatus;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'Delivery',
    operations: [
        new GetCollection(
            security: "is_granted('ROLE_ADMIN')",
            controller: \App\Ecommerce\Controller\Delivery\GetDeliveriesController::class,
            normalizationContext: ['groups' => ['delivery:read', 'delivery:list']],
        ),
        new Get(
            security: "is_granted('ROLE_ADMIN')",
            controller: \App\Ecommerce\Controller\Delivery\GetDeliveryController::class,
            normalizationContext: ['groups' => ['delivery:read', 'delivery:detail']],
        ),
        new Post(
            uriTemplate: '/deliveries/{id}/status',
            security: "is_granted('ROLE_ADMIN')",
            controller: \App\Ecommerce\Controller\Delivery\ChangeDeliveryStatusController::class,
            input: \App\Ecommerce\Dto\ChangeDeliveryStatusInput::class,
            name: 'api_delivery_change_status',
        ),
    ],
    formats: ['json' => ['application/json'], 'jsonld' => ['application/ld+json']],
    normalizationContext: ['groups' => ['delivery:read']],
    denormalizationContext: ['groups' => ['delivery:write']],
    paginationEnabled: true,
)]
class Delivery
{
    public ?int $id = null;

    public ?int $orderId = null;

    public array $shippingAddress = [];

    #[Assert\NotBlank(message: 'Le statut est requis')]
    #[Assert\Choice(
        callback: [DeliveryStatus::class, 'values'],
        message: 'Le statut doit être valide'
    )]
    public string $status = DeliveryStatus::PENDING->value;

    #[Assert\Length(max: 255)]
    public ?string $trackingNumber = null;

    #[Assert\Length(max: 255)]
    public ?string $carrier = null;

    public ?\DateTimeImmutable $shippedAt = null;

    public ?\DateTimeImmutable $deliveredAt = null;

    public ?\DateTimeImmutable $estimatedDeliveryDate = null;

    #[Assert\Length(max: 1000)]
    public ?string $notes = null;

    public ?\DateTimeImmutable $createdAt = null;

    public ?\DateTimeImmutable $updatedAt = null;
}
