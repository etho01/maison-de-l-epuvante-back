<?php

namespace App\Ecommerce\Dto;

use App\Ecommerce\Enum\OrderStatus;
use Symfony\Component\Validator\Constraints as Assert;

class ChangeOrderStatusInput
{
    #[Assert\NotBlank(message: 'Le statut est requis')]
    #[Assert\Choice(
        callback: [OrderStatus::class, 'values'],
        message: 'Le statut doit être: pending, paid, shipped, delivered, cancelled ou refunded'
    )]
    public string $status;

    #[Assert\Length(max: 1000)]
    public ?string $adminNotes = null;
}
