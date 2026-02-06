<?php

namespace App\Controller;

use App\ApiResource\SubscriptionPlan as SubscriptionPlanResource;
use App\Entity\SubscriptionPlan;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

#[AsController]
class UpdateSubscriptionPlanController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(#[MapRequestPayload] SubscriptionPlanResource $data, SubscriptionPlan $plan): JsonResponse
    {
        if ($data->name !== null) {
            $plan->setName($data->name);
        }
        if ($data->description !== null) {
            $plan->setDescription($data->description);
        }
        if ($data->price !== null) {
            $plan->setPrice($data->price);
        }
        if ($data->duration !== null) {
            $plan->setDuration($data->duration);
        }
        if ($data->durationUnit !== null) {
            $plan->setDurationUnit($data->durationUnit);
        }
        if ($data->features !== null) {
            $plan->setFeatures($data->features);
        }
        if ($data->isActive !== null) {
            $plan->setIsActive($data->isActive);
        }
        
        $plan->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->flush();

        return $this->json($plan, 200, [], ['groups' => ['subscription_plan:read', 'subscription_plan:detail']]);
    }
}
