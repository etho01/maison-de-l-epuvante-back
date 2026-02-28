<?php

namespace App\Ecommerce\Controller\Product;

use App\Ecommerce\Entity\Product;
use App\Enum\ApiError;
use App\Trait\ApiResponseTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class DeleteProductController extends AbstractController
{
    use ApiResponseTrait;

    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(Product $data): JsonResponse
    {
        // Vérifier si le produit est dans des commandes
        if ($data->getOrderItems()->count() > 0) {
            return $this->errorResponse(409, ApiError::PRODUCT_HAS_ORDERS, [
                'ordersCount' => $data->getOrderItems()->count()
            ]);
        }

        $this->entityManager->remove($data);
        $this->entityManager->flush();

        return $this->json(['code' => 204, 'data' => null], 204);
    }
}
