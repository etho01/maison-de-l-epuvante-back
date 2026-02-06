<?php

namespace App\Ecommerce\Controller;

use App\Ecommerce\Entity\Order;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class GetOrderController extends AbstractController
{
    public function __invoke(Order $data): JsonResponse
    {
        return $this->json($data, 200, [], ['groups' => ['order:read', 'order:detail']]);
    }
}
