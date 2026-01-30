<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\DigitalContent as DigitalContentEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    shortName: 'DigitalContent',
    operations: [
        new GetCollection(
            security: "is_granted('ROLE_USER')",
            normalizationContext: ['groups' => ['digital_content:read', 'digital_content:list']],
        ),
        new Get(
            security: "is_granted('ROLE_USER')",
            normalizationContext: ['groups' => ['digital_content:read', 'digital_content:detail']],
        ),
        new Get(
            uriTemplate: '/digital-contents/{id}/download',
            security: "is_granted('ROLE_USER')",
            name: 'api_digital_content_download',
        ),
    ],
    normalizationContext: ['groups' => ['digital_content:read']],
    paginationEnabled: true,
)]
class DigitalContent extends DigitalContentEntity
{
}
