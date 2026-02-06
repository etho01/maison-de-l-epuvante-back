<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class SubscriptionDto
{
    public ?int $id = null;

    #[Assert\NotBlank(message: 'L\'ID du plan est requis', groups: ['create'])]
    #[Assert\Positive(message: 'L\'ID du plan doit être positif')]
    public ?int $planId = null;

    public ?int $userId = null;

    #[Assert\Choice(
        choices: ['active', 'cancelled', 'expired', 'pending'],
        message: 'Le statut doit être active, cancelled, expired ou pending'
    )]
    public string $status = 'pending';

    #[Assert\Length(max: 255)]
    public ?string $stripeSubscriptionId = null;

    public ?\DateTimeInterface $startDate = null;

    public ?\DateTimeInterface $endDate = null;

    public bool $autoRenew = true;
}
