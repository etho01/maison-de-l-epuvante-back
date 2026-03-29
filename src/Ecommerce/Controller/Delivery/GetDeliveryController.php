<?php

namespace App\Ecommerce\Controller\Delivery;

use App\Ecommerce\Entity\Delivery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class GetDeliveryController extends AbstractController
{
    public function __invoke(Delivery $data): JsonResponse
    {
        return $this->json($data, 200, [], ['groups' => ['delivery:read', 'delivery:detail', 'delivery:orderItem', 'orderItem:read', 'orderItem:detail']]);
    }
}
