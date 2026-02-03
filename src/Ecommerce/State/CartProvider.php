<?php

namespace App\Ecommerce\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Ecommerce\Repository\CartRepository;

class CartProvider implements ProviderInterface
{
    public function __construct(
        private CartRepository $cartRepository
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if (!isset($uriVariables['id'])) {
            return $this->cartRepository->findAll();
        }

        return $this->cartRepository->find($uriVariables['id']);
    }
}
