<?php

namespace App\Ecommerce\Controller;

use App\Ecommerce\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class GetProductBySlugController extends AbstractController
{
    public function __construct(
        private ProductRepository $productRepository,
        private SerializerInterface $serializer
    ) {
    }

    public function __invoke(string $slug): JsonResponse
    {
        $product = $this->productRepository->findOneBySlug($slug);

        if (!$product) {
            throw new NotFoundHttpException('Product not found');
        }

        $data = $this->serializer->serialize(
            $product,
            'json',
            ['groups' => ['product:read', 'product:detail']]
        );

        return new JsonResponse($data, 200, [], true);
    }
}
