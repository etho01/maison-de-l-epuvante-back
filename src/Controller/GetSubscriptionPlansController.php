<?php

namespace App\Controller;

use App\Repository\SubscriptionPlanRepository;
use App\Service\PaginationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class GetSubscriptionPlansController extends AbstractController
{
    public function __construct(
        private SubscriptionPlanRepository $subscriptionPlanRepository,
        private PaginationService $paginationService
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $itemsPerPage = max(1, min(100, (int) $request->query->get('itemsPerPage', 30)));
        $enablePagination = filter_var($request->query->get('pagination', 'true'), FILTER_VALIDATE_BOOLEAN);

        $queryBuilder = $this->subscriptionPlanRepository->createQueryBuilder('sp');
        $result = $this->paginationService->paginate($queryBuilder, $page, $itemsPerPage, $enablePagination);

        return $this->json($result, 200, [], ['groups' => ['subscription_plan:read', 'subscription_plan:list']]);
    }
}
