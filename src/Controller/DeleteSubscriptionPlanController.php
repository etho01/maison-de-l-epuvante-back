<?php

namespace App\Controller;

use App\Entity\SubscriptionPlan;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class DeleteSubscriptionPlanController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(SubscriptionPlan $data): JsonResponse
    {
        $this->entityManager->remove($data);
        $this->entityManager->flush();

        return $this->json(['message' => 'Plan d\'abonnement supprimé avec succès'], 204);
    }
}
