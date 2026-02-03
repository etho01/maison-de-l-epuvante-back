<?php

namespace App\Ecommerce\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Ecommerce\Repository\OrderRepository;

class OrderProvider implements ProviderInterface
{
    public function __construct(
        private OrderRepository $orderRepository
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if (!isset($uriVariables['id'])) {
            return $this->orderRepository->findAll();
        }

        return $this->orderRepository->find($uriVariables['id']);
    }
}
