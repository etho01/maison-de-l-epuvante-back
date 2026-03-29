<?php

namespace App\Ecommerce\Controller\Delivery;

use App\Ecommerce\Repository\DeliveryRepository;
use App\Service\PaginationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class GetDeliveriesController extends AbstractController
{
    public function __construct(
        private DeliveryRepository $deliveryRepository,
        private PaginationService $paginationService
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $itemsPerPage = max(1, min(100, (int) $request->query->get('itemsPerPage', 30)));
        $enablePagination = filter_var($request->query->get('pagination', 'true'), FILTER_VALIDATE_BOOLEAN);
        $status = $request->query->get('status');

        $queryBuilder = $this->deliveryRepository->createQueryBuilder('d')
            ->leftJoin('d.order', 'o')
            ->addSelect('o')
            ->orderBy('d.createdAt', 'DESC');

        // Filtrer par statut si fourni
        if ($status) {
            $queryBuilder->andWhere('d.status = :status')
                ->setParameter('status', $status);
        }

        $result = $this->paginationService->paginate($queryBuilder, $page, $itemsPerPage, $enablePagination);

        return $this->json($result, 200, [], ['groups' => ['delivery:read', 'delivery:list', 'delivery:orderItem', 'orderItem:read', 'orderItem:detail']]);
    }
}
