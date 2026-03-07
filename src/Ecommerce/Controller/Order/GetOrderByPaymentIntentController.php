<?php

namespace App\Ecommerce\Controller\Order;

use App\Enum\ApiError;
use App\Trait\ApiResponseTrait;
use App\Ecommerce\Repository\OrderRepository;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class GetOrderByPaymentIntentController extends AbstractController
{
    use ApiResponseTrait;

    public function __construct(
        private OrderRepository $orderRepository,
        private Security $security,
    ) {}

    public function __invoke(string $paymentIntentId): JsonResponse
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            return $this->errorResponse(401, ApiError::USER_NOT_AUTHENTICATED);
        }

        $order = $this->orderRepository->findByPaymentIntentId($paymentIntentId);

        if (!$order) {
            return $this->errorResponse(404, ApiError::ORDER_NOT_FOUND, [
                'paymentIntentId' => $paymentIntentId
            ]);
        }

        // Vérifier que l'utilisateur est admin ou propriétaire de la commande
        if (!$this->security->isGranted('ROLE_ADMIN') && $order->getUser() !== $user) {
            return $this->errorResponse(403, ApiError::ACCESS_DENIED);
        }

        return $this->json($order, 200, [], ['groups' => ['order:read', 'order:detail', 'user:read']]);
    }
}
