<?php

namespace App\Controller\Administrator;

use App\ApiResource\Administrator as AdministratorResource;
use App\Enum\ApiError;
use App\Trait\ApiResponseTrait;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class GetAdministratorController extends AbstractController
{
    use ApiResponseTrait;

    public function __construct(
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

        $resource = new AdministratorResource();
        $resource->id = $admin->getId();
        $resource->email = $admin->getEmail();
        $resource->firstName = $admin->getFirstName();
        $resource->lastName = $admin->getLastName();
        $resource->roles = $admin->getRoles();
        $resource->isVerified = $admin->isVerified();
        $resource->createdAt = $admin->getCreatedAt();
        $resource->updatedAt = $admin->getUpdatedAt();

        return $this->json([
            'id' => $resource->id,
            'email' => $resource->email,
            'firstName' => $resource->firstName,
            'lastName' => $resource->lastName,
            'roles' => $resource->roles,
            'isVerified' => $resource->isVerified,
            'createdAt' => $resource->createdAt?->format('c'),
            'updatedAt' => $resource->updatedAt?->format('c'),
        ]);
    }
}
