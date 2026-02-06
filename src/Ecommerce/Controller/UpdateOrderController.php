<?php

namespace App\Ecommerce\Controller;

use App\Ecommerce\ApiResource\Order as OrderResource;
use App\Ecommerce\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

#[AsController]
class UpdateOrderController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(#[MapRequestPayload] OrderResource $data, Order $order): JsonResponse
    {
        if ($data->status !== null) {
            $order->setStatus($data->status);
        }
        
        $order->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->flush();

        return $this->json($order, 200, [], ['groups' => ['order:read', 'order:detail']]);
    }
}
