<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class DigitalContentDto
{
    public ?int $id = null;

    #[Assert\NotBlank(message: 'Le nom est requis')]
    #[Assert\Length(min: 3, max: 255)]
    public ?string $name = null;

    #[Assert\Length(max: 2000)]
    public ?string $description = null;

    #[Assert\NotBlank(message: 'Le chemin du fichier est requis')]
    #[Assert\Length(max: 500)]
    public ?string $filePath = null;

    #[Assert\NotBlank(message: 'Le type de contenu est requis')]
    #[Assert\Choice(
        choices: ['video', 'audio', 'document', 'image', 'archive'],
        message: 'Le type doit être video, audio, document, image ou archive'
    )]
    public ?string $contentType = null;

    #[Assert\PositiveOrZero]
    public ?int $fileSize = null;

    public ?int $productId = null;

    public ?int $subscriptionPlanId = null;

    public bool $requiresSubscription = false;
}
