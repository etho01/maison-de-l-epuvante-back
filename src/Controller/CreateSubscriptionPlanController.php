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
class CreateSubscriptionPlanController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function __invoke(#[MapRequestPayload] SubscriptionPlanResource $data): JsonResponse
    {
        $plan = new SubscriptionPlan();
        $plan->setName($data->name);
        $plan->setDescription($data->description);
        $plan->setPrice((string) $data->price);
        
        $plan->setDurationInMonths($data->durationInMonths);
        
        $plan->setBillingInterval($data->billingInterval);
        
        $plan->setActive($data->isActive ?? true);
        $plan->setFormat('both'); // Valeur par défaut

        $this->entityManager->persist($plan);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Plan d\'abonnement créé avec succès',
            'id' => $plan->getId(),
            'plan' => [
                'id' => $plan->getId(),
                'name' => $plan->getName(),
                'price' => $plan->getPrice(),
                'durationInMonths' => $plan->getDurationInMonths(),
                'billingInterval' => $plan->getBillingInterval()
            ]
        ], 201);
    }
}
