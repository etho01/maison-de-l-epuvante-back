<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\PaginationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class GetUsersController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
        private PaginationService $paginationService
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $itemsPerPage = max(1, min(100, (int) $request->query->get('itemsPerPage', 30)));
        $enablePagination = filter_var($request->query->get('pagination', 'true'), FILTER_VALIDATE_BOOLEAN);

        $queryBuilder = $this->userRepository->createQueryBuilder('u');
        $result = $this->paginationService->paginate($queryBuilder, $page, $itemsPerPage, $enablePagination);

        return $this->json($result, 200, [], ['groups' => ['user:read', 'user:list']]);
    }
}
