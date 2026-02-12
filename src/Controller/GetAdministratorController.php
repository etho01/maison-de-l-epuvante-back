<?php

namespace App\Controller;

use App\ApiResource\Administrator as AdministratorResource;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[AsController]
class GetAdministratorController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository
    ) {}

    public function __invoke(int $id): JsonResponse
    {
        $admin = $this->userRepository->find($id);

        if (!$admin) {
            throw new NotFoundHttpException('Administrateur non trouvé');
        }

        // Vérifier que l'utilisateur est bien un administrateur
        if (!in_array('ROLE_ADMIN', $admin->getRoles())) {
            throw new NotFoundHttpException('Cet utilisateur n\'est pas un administrateur');
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
