<?php

namespace App\Controller;

use App\Entity\SubscriptionPlan;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class GetSubscriptionPlanController extends AbstractController
{
    public function __invoke(SubscriptionPlan $data): JsonResponse
    {
        return $this->json($data, 200, [], ['groups' => ['subscription_plan:read', 'subscription_plan:detail']]);
    }
}
