<?php

namespace App\Ecommerce\Controller;

use App\Ecommerce\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class GetProductController extends AbstractController
{
    public function __invoke(Product $data): JsonResponse
    {
        return $this->json($data, 200, [], ['groups' => ['product:read', 'product:detail']]);
    }
}
