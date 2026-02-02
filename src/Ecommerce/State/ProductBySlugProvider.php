<?php

namespace App\Ecommerce\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Ecommerce\Repository\ProductRepository;

class ProductBySlugProvider implements ProviderInterface
{
    public function __construct(
        private ProductRepository $productRepository
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?object
    {
        $slug = $uriVariables['slug'] ?? null;
        
        if (!$slug) {
            return null;
        }

        return $this->productRepository->findOneBySlug($slug);
    }
}
