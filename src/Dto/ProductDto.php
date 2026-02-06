<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class ProductDto
{
    public ?int $id = null;

    #[Assert\NotBlank(message: 'Le nom du produit est requis')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Le nom doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères'
    )]
    public ?string $name = null;

    #[Assert\Length(max: 5000, maxMessage: 'La description ne peut pas dépasser {{ limit }} caractères')]
    public ?string $description = null;

    #[Assert\NotBlank(message: 'Le slug est requis')]
    #[Assert\Regex(
        pattern: '/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
        message: 'Le slug doit être en minuscules avec des tirets uniquement'
    )]
    public ?string $slug = null;

    #[Assert\NotBlank(message: 'Le prix est requis')]
    #[Assert\Positive(message: 'Le prix doit être positif')]
    #[Assert\Range(
        min: 0.01,
        max: 999999.99,
        notInRangeMessage: 'Le prix doit être entre {{ min }} et {{ max }}'
    )]
    public ?float $price = null;

    #[Assert\NotNull(message: 'Le stock est requis')]
    #[Assert\PositiveOrZero(message: 'Le stock ne peut pas être négatif')]
    #[Assert\Range(
        min: 0,
        max: 100000,
        notInRangeMessage: 'Le stock doit être entre {{ min }} et {{ max }}'
    )]
    public int $stock = 0;

    #[Assert\NotBlank(message: 'Le type est requis')]
    #[Assert\Choice(
        choices: ['physical', 'digital', 'subscription'],
        message: 'Le type doit être physical, digital ou subscription'
    )]
    public string $type = 'physical';

    #[Assert\Length(max: 255)]
    public ?string $sku = null;

    public ?array $images = [];

    public bool $active = true;

    public bool $exclusiveOnline = false;

    #[Assert\Positive(message: 'L\'ID de catégorie doit être positif')]
    public ?int $categoryId = null;

    #[Assert\PositiveOrZero(message: 'Le poids ne peut pas être négatif')]
    public ?float $weight = null;

    public ?array $metadata = [];
}
