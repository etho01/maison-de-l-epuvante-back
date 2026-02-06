<?php

namespace App\Controller;

use App\Entity\Subscription;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class RenewSubscriptionController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(Subscription $data): JsonResponse
    {
        $data->setStatus('active');
        $data->setUpdatedAt(new \DateTimeImmutable());
        
        // Prolonger l'abonnement selon la durée du plan
        if ($data->getEndDate() && $data->getPlan()) {
            $plan = $data->getPlan();
            $interval = new \DateInterval('P' . $plan->getDuration() . 'D');
            $newEndDate = $data->getEndDate()->add($interval);
            $data->setEndDate($newEndDate);
        }
        
        $this->entityManager->flush();

        return $this->json($data, 200, [], ['groups' => ['subscription:read', 'subscription:detail']]);
    }
}
