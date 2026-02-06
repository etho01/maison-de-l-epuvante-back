<?php

namespace App\Ecommerce\Controller;

use App\Ecommerce\Repository\OrderRepository;
use App\Service\PaginationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class GetOrdersController extends AbstractController
{
    public function __construct(
        private OrderRepository $orderRepository,
        private Security $security,
        private PaginationService $paginationService
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $itemsPerPage = max(1, min(100, (int) $request->query->get('itemsPerPage', 30)));
        $enablePagination = filter_var($request->query->get('pagination', 'true'), FILTER_VALIDATE_BOOLEAN);

        $user = $this->security->getUser();
        
        $queryBuilder = $this->orderRepository->createQueryBuilder('o');
        
        if (!$this->isGranted('ROLE_ADMIN')) {
            $queryBuilder->where('o.user = :user')
                ->setParameter('user', $user);
        }
        
        $result = $this->paginationService->paginate($queryBuilder, $page, $itemsPerPage, $enablePagination);
        
        return $this->json($result, 200, [], ['groups' => ['order:read', 'order:list']]);
    }
}
