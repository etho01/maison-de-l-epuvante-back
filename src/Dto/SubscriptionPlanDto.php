<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class SubscriptionPlanDto
{
    public ?int $id = null;

    #[Assert\NotBlank(message: 'Le nom du plan est requis')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Le nom doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères'
    )]
    public ?string $name = null;

    #[Assert\Length(max: 2000)]
    public ?string $description = null;

    #[Assert\NotBlank(message: 'Le prix est requis')]
    #[Assert\Positive(message: 'Le prix doit être positif')]
    #[Assert\Range(min: 0.01, max: 99999.99)]
    public ?float $price = null;

    #[Assert\NotBlank(message: 'La durée est requise')]
    #[Assert\Positive(message: 'La durée doit être positive')]
    #[Assert\Range(min: 1, max: 365)]
    public ?int $duration = null;

    #[Assert\NotBlank(message: 'L\'unité de durée est requise')]
    #[Assert\Choice(
        choices: ['day', 'week', 'month', 'year'],
        message: 'L\'unité doit être day, week, month ou year'
    )]
    public string $durationUnit = 'month';

    public bool $active = true;

    public ?array $features = [];
}
