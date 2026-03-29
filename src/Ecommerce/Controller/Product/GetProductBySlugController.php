<?php

namespace App\Ecommerce\Controller\Product;

use App\Enum\ApiError;
use App\Trait\ApiResponseTrait;
use App\Ecommerce\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Loader\Configurator\App;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;

class GetProductBySlugController extends AbstractController
{
    use ApiResponseTrait;

    public function __construct(
        private ProductRepository $productRepository,
        private SerializerInterface $serializer
    ) {
    }

    public function __invoke(string $slug): JsonResponse
    {
        $product = $this->productRepository->findOneBySlug($slug);

        if (!$product) {
            return $this->errorResponse(404, ApiError::PRODUCT_NOT_FOUND, ['slug' => $slug]);
        }

        $data = $this->serializer->normalize(
            $product,
            null,
            ['groups' => ['product:read', 'product:detail']]
        );

        return $this->successResponse($data);
    }
}
