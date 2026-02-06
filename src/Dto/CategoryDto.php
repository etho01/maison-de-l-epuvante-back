<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CategoryDto
{
    public ?int $id = null;

    #[Assert\NotBlank(message: 'Le nom de la catégorie est requis')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Le nom doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères'
    )]
    public ?string $name = null;

    #[Assert\Length(max: 2000, maxMessage: 'La description ne peut pas dépasser {{ limit }} caractères')]
    public ?string $description = null;

    #[Assert\NotBlank(message: 'Le slug est requis')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Le slug doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le slug ne peut pas dépasser {{ limit }} caractères'
    )]
    #[Assert\Regex(
        pattern: '/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
        message: 'Le slug doit être en minuscules avec des tirets uniquement'
    )]
    public ?string $slug = null;

    public ?int $parentId = null;
}
