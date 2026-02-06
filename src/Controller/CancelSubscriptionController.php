<?php

namespace App\Controller;

use App\Entity\Subscription;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class CancelSubscriptionController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(Subscription $data): JsonResponse
    {
        $data->setStatus('cancelled');
        $data->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->flush();

        return $this->json($data, 200, [], ['groups' => ['subscription:read', 'subscription:detail']]);
    }
}
