<?php

namespace App\Controller;

use App\Entity\Subscription;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class GetSubscriptionController extends AbstractController
{
    public function __invoke(Subscription $data): JsonResponse
    {
        return $this->json($data, 200, [], ['groups' => ['subscription:read', 'subscription:detail']]);
    }
}
