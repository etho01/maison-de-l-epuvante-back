<?php

namespace App\Ecommerce\Dto;

use App\Ecommerce\Enum\DeliveryStatus;
use Symfony\Component\Validator\Constraints as Assert;

class ChangeDeliveryStatusInput
{
    #[Assert\NotBlank(message: 'Le statut est requis')]
    #[Assert\Choice(
        callback: [DeliveryStatus::class, 'values'],
        message: 'Le statut doit être: pending, preparing, shipped, in_transit, delivered ou failed'
    )]
    public string $status;

    #[Assert\Length(max: 255)]
    public ?string $trackingNumber = null;

    #[Assert\Length(max: 255)]
    public ?string $carrier = null;

    #[Assert\Length(max: 1000)]
    public ?string $notes = null;
}
