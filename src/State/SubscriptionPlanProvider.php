<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\SubscriptionPlanRepository;

class SubscriptionPlanProvider implements ProviderInterface
{
    public function __construct(
        private SubscriptionPlanRepository $subscriptionPlanRepository
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if (!isset($uriVariables['id'])) {
            return $this->subscriptionPlanRepository->findAll();
        }

        return $this->subscriptionPlanRepository->find($uriVariables['id']);
    }
}
