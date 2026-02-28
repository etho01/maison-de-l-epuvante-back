<?php

namespace App\Controller\User;

use App\Entity\User;
use App\Enum\ApiError;
use App\Trait\ApiResponseTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class DeleteUserController extends AbstractController
{
    use ApiResponseTrait;

    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(User $data): JsonResponse
    {
        // Vérifier si l'utilisateur a des commandes
        if ($data->getOrders()->count() > 0) {
            return $this->errorResponse(409, ApiError::USER_HAS_ORDERS, [
                'ordersCount' => $data->getOrders()->count()
            ]);
        }

        // Vérifier si l'utilisateur a des abonnements
        if ($data->getSubscriptions()->count() > 0) {
            return $this->errorResponse(409, ApiError::USER_HAS_SUBSCRIPTIONS, [
                'subscriptionsCount' => $data->getSubscriptions()->count()
            ]);
        }

        $this->entityManager->remove($data);
        $this->entityManager->flush();

        return $this->json(['code' => 204, 'data' => null], 204);
    }
}
