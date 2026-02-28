<?php

namespace App\Controller\Subscription;

use App\Entity\SubscriptionPlan;
use App\Enum\ApiError;
use App\Trait\ApiResponseTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class DeleteSubscriptionPlanController extends AbstractController
{
    use ApiResponseTrait;

    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(SubscriptionPlan $data): JsonResponse
    {
        // Vérifier si le plan a des abonnements actifs
        if ($data->getSubscriptions()->count() > 0) {
            return $this->errorResponse(409, ApiError::SUBSCRIPTION_PLAN_HAS_SUBSCRIPTIONS, [
                'subscriptionsCount' => $data->getSubscriptions()->count()
            ]);
        }

        $this->entityManager->remove($data);
        $this->entityManager->flush();

        return $this->json(['code' => 204, 'data' => null], 204);
    }
}
