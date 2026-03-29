<?php

namespace App\Ecommerce\Controller\Delivery;

use App\Trait\ApiResponseTrait;
use App\Ecommerce\Dto\ChangeDeliveryStatusInput;
use App\Ecommerce\Entity\Delivery;
use App\Ecommerce\Enum\DeliveryStatus;
use App\Ecommerce\Enum\OrderStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

#[AsController]
class ChangeDeliveryStatusController extends AbstractController
{
    use ApiResponseTrait;

    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function __invoke(
        Delivery $delivery,
        #[MapRequestPayload] ChangeDeliveryStatusInput $data
    ): JsonResponse {
        $previousStatus = $delivery->getStatus();
        $newStatus = $data->status;

        // Mettre à jour le statut
        $delivery->setStatus($newStatus);
        $delivery->setUpdatedAt(new \DateTimeImmutable());

        // Mettre à jour le numéro de suivi et transporteur si fournis
        if ($data->trackingNumber !== null) {
            $delivery->setTrackingNumber($data->trackingNumber);
        }

        if ($data->carrier !== null) {
            $delivery->setCarrier($data->carrier);
        }

        // Mettre à jour les notes si fournies
        if ($data->notes !== null) {
            $delivery->setNotes($data->notes);
        }

        // Logique métier selon le statut
        switch ($newStatus) {
            case DeliveryStatus::SHIPPED->value:
                if (!$delivery->getShippedAt()) {
                    $delivery->setShippedAt(new \DateTimeImmutable());
                }
                // Mettre à jour le statut de la commande
                if ($delivery->getOrder()) {
                    $delivery->getOrder()->setStatus(OrderStatus::SHIPPED->value);
                    $delivery->getOrder()->setUpdatedAt(new \DateTimeImmutable());
                }
                break;

            case DeliveryStatus::DELIVERED->value:
                if (!$delivery->getDeliveredAt()) {
                    $delivery->setDeliveredAt(new \DateTimeImmutable());
                }
                // Mettre à jour le statut de la commande
                if ($delivery->getOrder()) {
                    $delivery->getOrder()->setStatus(OrderStatus::DELIVERED->value);
                    $delivery->getOrder()->setUpdatedAt(new \DateTimeImmutable());
                }
                break;
        }

        $this->entityManager->flush();

        return $this->successResponse([
            'delivery' => [
                'id' => $delivery->getId(),
                'orderId' => $delivery->getOrder()?->getId(),
                'orderNumber' => $delivery->getOrder()?->getOrderNumber(),
                'previousStatus' => $previousStatus,
                'newStatus' => $newStatus,
                'trackingNumber' => $delivery->getTrackingNumber(),
                'carrier' => $delivery->getCarrier(),
                'updatedAt' => $delivery->getUpdatedAt()->format('c')
            ],
            'message' => "Le statut de la livraison a été changé de '{$previousStatus}' à '{$newStatus}'"
        ]);
    }
}
