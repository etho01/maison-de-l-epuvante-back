<?php

namespace App\Controller;

use App\Entity\DigitalContent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class GetDigitalContentController extends AbstractController
{
    public function __invoke(DigitalContent $data): JsonResponse
    {
        return $this->json($data, 200, [], ['groups' => ['digital_content:read', 'digital_content:detail']]);
    }
}
