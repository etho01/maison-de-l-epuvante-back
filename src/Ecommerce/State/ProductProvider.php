<?php

namespace App\Ecommerce\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Ecommerce\Repository\ProductRepository;

class ProductProvider implements ProviderInterface
{
    public function __construct(
        private ProductRepository $productRepository
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if (!isset($uriVariables['id'])) {
            return $this->productRepository->findAll();
        }

        return $this->productRepository->find($uriVariables['id']);
    }
}
