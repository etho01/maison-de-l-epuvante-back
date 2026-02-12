<?php

namespace App\Controller;

use App\ApiResource\Administrator as AdministratorResource;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class GetAdministratorsController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository
    ) {}

    public function __invoke(): JsonResponse
    {
        // Récupérer uniquement les utilisateurs ayant ROLE_ADMIN
        $administrators = $this->userRepository->findByRole('ROLE_ADMIN');

        $data = array_map(function($admin) {
            $resource = new AdministratorResource();
            $resource->id = $admin->getId();
            $resource->email = $admin->getEmail();
            $resource->firstName = $admin->getFirstName();
            $resource->lastName = $admin->getLastName();
            $resource->roles = $admin->getRoles();
            $resource->isVerified = $admin->isVerified();
            $resource->createdAt = $admin->getCreatedAt();
            $resource->updatedAt = $admin->getUpdatedAt();
            
            return [
                'id' => $resource->id,
                'email' => $resource->email,
                'firstName' => $resource->firstName,
                'lastName' => $resource->lastName,
                'roles' => $resource->roles,
                'isVerified' => $resource->isVerified,
                'createdAt' => $resource->createdAt?->format('c'),
                'updatedAt' => $resource->updatedAt?->format('c'),
            ];
        }, $administrators);

        return $this->json($data);
    }
}
