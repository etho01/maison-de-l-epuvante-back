<?php

namespace App\Ecommerce\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class OrderCheckoutInput
{
    #[Assert\NotBlank]
    #[Assert\Collection(
        fields: [
            'firstName' => new Assert\NotBlank(),
            'lastName' => new Assert\NotBlank(),
            'address' => new Assert\NotBlank(),
            'city' => new Assert\NotBlank(),
            'postalCode' => new Assert\NotBlank(),
            'country' => new Assert\NotBlank(),
        ],
        allowExtraFields: false,
        allowMissingFields: false
    )]
    public array $shippingAddress = [];

    #[Assert\NotBlank]
    #[Assert\Collection(
        fields: [
            'firstName' => new Assert\NotBlank(),
            'lastName' => new Assert\NotBlank(),
            'address' => new Assert\NotBlank(),
            'city' => new Assert\NotBlank(),
            'postalCode' => new Assert\NotBlank(),
            'country' => new Assert\NotBlank(),
        ],
        allowExtraFields: false,
        allowMissingFields: false
    )]
    public array $billingAddress = [];

    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    public string $paymentMethod = 'card';

    #[Assert\Length(max: 1000)]
    public ?string $customerNotes = null;

    #[Assert\NotBlank]
    #[Assert\Count(min: 1, minMessage: 'Au moins un produit est requis')]
    #[Assert\All([
        new Assert\Collection(
            fields: [
                'id' => new Assert\Positive(),
                'name' => new Assert\NotBlank(),
                'quantity' => new Assert\Positive(),
                'price' => new Assert\PositiveOrZero(),
            ],
            allowExtraFields: false,
            allowMissingFields: false
        )
    ])]
    public array $products = [];
}
