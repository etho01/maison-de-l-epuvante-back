<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class OrderDto
{
    public ?int $id = null;

    public ?int $userId = null;

    #[Assert\NotBlank(message: 'Le statut est requis')]
    #[Assert\Choice(
        choices: ['pending', 'processing', 'completed', 'cancelled', 'refunded'],
        message: 'Le statut doit être pending, processing, completed, cancelled ou refunded'
    )]
    public string $status = 'pending';

    #[Assert\NotBlank(message: 'L\'adresse de livraison est requise')]
    #[Assert\Length(max: 500)]
    public ?string $shippingAddress = null;

    #[Assert\NotBlank(message: 'L\'adresse de facturation est requise')]
    #[Assert\Length(max: 500)]
    public ?string $billingAddress = null;

    #[Assert\Length(max: 50)]
    public ?string $paymentMethod = null;

    #[Assert\Length(max: 255)]
    public ?string $paymentIntentId = null;

    public array $items = [];
}
