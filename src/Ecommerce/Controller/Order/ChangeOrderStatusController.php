<?php

namespace App\Ecommerce\Controller\Order;

use App\Enum\ApiError;
use App\Trait\ApiResponseTrait;
use App\Ecommerce\Dto\ChangeOrderStatusInput;
use App\Ecommerce\Entity\Order;
use App\Ecommerce\Enum\OrderStatus;
use App\Ecommerce\Enum\DeliveryStatus;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

#[AsController]
class ChangeOrderStatusController extends AbstractController
{
    use ApiResponseTrait;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security
    ) {}

    public function __invoke(
        Order $order,
        #[MapRequestPayload] ChangeOrderStatusInput $data
    ): JsonResponse {
        $user = $this->security->getUser();
        $isAdmin = $this->security->isGranted('ROLE_ADMIN');
        
        // Vérifier que l'utilisateur est admin ou propriétaire de la commande
        if (!$user instanceof User || (!$isAdmin && $order->getUser() !== $user)) {
            return $this->errorResponse(403, ApiError::ACCESS_DENIED);
        }
        
        $previousStatus = $order->getStatus();
        $newStatus = $data->status;
        
        // Les clients ne peuvent changer le statut qu'en "cancelled"
        if (!$isAdmin && $newStatus !== OrderStatus::CANCELLED->value) {
            return $this->errorResponse(403, ApiError::ACCESS_DENIED, [
                'message' => 'Les clients peuvent uniquement annuler leurs commandes'
            ]);
        }
        
        // Empêcher l'annulation de commandes déjà livrées ou remboursées
        if ($newStatus === OrderStatus::CANCELLED->value && 
            in_array($previousStatus, [OrderStatus::DELIVERED->value, OrderStatus::REFUNDED->value])) {
            return $this->errorResponse(400, ApiError::INVALID_STATUS_TRANSITION, [
                'message' => "Impossible d'annuler une commande déjà {$previousStatus}"
            ]);
        }

        // Mettre à jour le statut
        $order->setStatus($newStatus);
        $order->setUpdatedAt(new \DateTimeImmutable());

        // Logique métier selon le statut
        switch ($newStatus) {
            case OrderStatus::PAID->value:
                if (!$order->getPaidAt()) {
                    $order->setPaidAt(new \DateTimeImmutable());
                }
                if (!$order->hasPhysicalProducts()) {
                    // Si la commande ne contient que des produits digitaux, la marquer comme livrée immédiatement
                    $order->setStatus(OrderStatus::DELIVERED->value);
                }
                break;
                
            case OrderStatus::SHIPPED->value:
                // Si la commande a une livraison, mettre à jour son statut
                if ($order->getDelivery()) {
                    $order->getDelivery()->setStatus(DeliveryStatus::SHIPPED->value);
                    $order->getDelivery()->setShippedAt(new \DateTimeImmutable());
                }
                break;
                
            case OrderStatus::DELIVERED->value:
                // Si la commande a une livraison, marquer comme livrée
                if ($order->getDelivery()) {
                    $order->getDelivery()->setStatus(DeliveryStatus::DELIVERED->value);
                    $order->getDelivery()->setDeliveredAt(new \DateTimeImmutable());
                }
                break;
        }

        $this->entityManager->flush();

        return $this->successResponse([
            'order' => [
                'id' => $order->getId(),
                'orderNumber' => $order->getOrderNumber(),
                'previousStatus' => $previousStatus,
                'newStatus' => $newStatus,
                'updatedAt' => $order->getUpdatedAt()->format('c')
            ],
            'message' => "Le statut de la commande a été changé de '{$previousStatus}' à '{$newStatus}'"
        ]);
    }
}
