<?php

namespace App\Controller;

use App\ApiResource\Subscription as SubscriptionResource;
use App\Entity\Subscription;
use App\Entity\SubscriptionPlan;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Bundle\SecurityBundle\Security;

#[AsController]
class CreateSubscriptionController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security
    ) {}

    public function __invoke(#[MapRequestPayload] SubscriptionResource $data): JsonResponse
    {
        $user = $this->security->getUser();
        
        if (!$user instanceof User) {
            return $this->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        $plan = $this->entityManager->getRepository(SubscriptionPlan::class)->find($data->planId);
        
        if (!$plan) {
            return $this->json(['error' => 'Plan d\'abonnement non trouvé'], 404);
        }

        $subscription = new Subscription();
        $subscription->setUser($user);
        $subscription->setPlan($plan);
        $subscription->setStatus($data->status);
        $subscription->setStartDate($data->startDate ?? new \DateTimeImmutable());
        $subscription->setAutoRenew($data->autoRenew);
        
        if ($data->stripeSubscriptionId) {
            $subscription->setStripeSubscriptionId($data->stripeSubscriptionId);
        }

        // Calculate end date based on plan duration
        $startDate = $data->startDate ?? new \DateTimeImmutable();
        $endDate = match($plan->getDurationUnit()) {
            'day' => $startDate->modify('+' . $plan->getDuration() . ' days'),
            'week' => $startDate->modify('+' . $plan->getDuration() . ' weeks'),
            'month' => $startDate->modify('+' . $plan->getDuration() . ' months'),
            'year' => $startDate->modify('+' . $plan->getDuration() . ' years'),
            default => $startDate->modify('+1 month')
        };
        $subscription->setEndDate($endDate);

        $this->entityManager->persist($subscription);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Abonnement créé avec succès',
            'id' => $subscription->getId(),
            'subscription' => [
                'id' => $subscription->getId(),
                'status' => $subscription->getStatus(),
                'startDate' => $subscription->getStartDate()?->format('Y-m-d'),
                'endDate' => $subscription->getEndDate()?->format('Y-m-d')
            ]
        ], 201);
    }
}
