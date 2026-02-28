<?php

namespace App\Ecommerce\Controller\Category;

use App\Ecommerce\Entity\Category;
use App\Enum\ApiError;
use App\Trait\ApiResponseTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class DeleteCategoryController extends AbstractController
{
    use ApiResponseTrait;

    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(Category $data): JsonResponse
    {
        // Vérifier si la catégorie a des produits
        if ($data->getProducts()->count() > 0) {
            return $this->errorResponse(409, ApiError::CATEGORY_HAS_PRODUCTS, [
                'productsCount' => $data->getProducts()->count()
            ]);
        }

        // Vérifier si la catégorie a des sous-catégories
        if ($data->getChildren()->count() > 0) {
            return $this->errorResponse(409, ApiError::CATEGORY_HAS_CHILDREN, [
                'childrenCount' => $data->getChildren()->count()
            ]);
        }

        $this->entityManager->remove($data);
        $this->entityManager->flush();

        return $this->json(['code' => 204, 'data' => null], 204);
    }
}
