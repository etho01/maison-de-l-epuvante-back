<?php

namespace App\Ecommerce\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Ecommerce\Repository\CategoryRepository;

class CategoryProvider implements ProviderInterface
{
    public function __construct(
        private CategoryRepository $categoryRepository
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if (!isset($uriVariables['id'])) {
            return $this->categoryRepository->findAll();
        }

        return $this->categoryRepository->find($uriVariables['id']);
    }
}
