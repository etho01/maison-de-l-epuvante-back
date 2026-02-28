<?php

namespace App\Controller\Administrator;

use App\Enum\ApiError;
use App\Trait\ApiResponseTrait;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class DeleteAdministratorController extends AbstractController
{
    use ApiResponseTrait;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository
    ) {}

    public function __invoke(int $id): JsonResponse
    {
        $admin = $this->userRepository->find($id);

        if (!$admin) {
            return $this->errorResponse(404, ApiError::ADMINISTRATOR_NOT_FOUND);
        }

        // Vérifier que l'utilisateur est bien un administrateur
        if (!in_array('ROLE_ADMIN', $admin->getRoles())) {
            return $this->errorResponse(404, ApiError::NOT_AN_ADMINISTRATOR);
        }

        // Empêcher la suppression du dernier administrateur
        $allAdmins = $this->userRepository->findByRole('ROLE_ADMIN');
        if (count($allAdmins) <= 1) {
            return $this->errorResponse(400, ApiError::CANNOT_DELETE_LAST_ADMINISTRATOR);
        }

        // Vérifier si l'administrateur a des commandes
        if ($admin->getOrders()->count() > 0) {
            return $this->errorResponse(409, ApiError::USER_HAS_ORDERS, [
                'ordersCount' => $admin->getOrders()->count()
            ]);
        }

        // Vérifier si l'administrateur a des abonnements
        if ($admin->getSubscriptions()->count() > 0) {
            return $this->errorResponse(409, ApiError::USER_HAS_SUBSCRIPTIONS, [
                'subscriptionsCount' => $admin->getSubscriptions()->count()
            ]);
        }

        $this->entityManager->remove($admin);
        $this->entityManager->flush();

        return $this->json(['code' => 204, 'data' => null], 204);
    }
}
