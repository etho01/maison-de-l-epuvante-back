<?php

namespace App\Controller;

use App\ApiResource\Administrator as AdministratorResource;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsController]
class CreateAdministratorController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function __invoke(#[MapRequestPayload] AdministratorResource $data): JsonResponse
    {
        // Vérifier si l'email existe déjà
        $existingUser = $this->entityManager->getRepository(User::class)
            ->findOneBy(['email' => $data->email]);
        
        if ($existingUser) {
            return $this->json([
                'error' => 'Un utilisateur avec cet email existe déjà'
            ], 400);
        }

        $admin = new User();
        $admin->setEmail($data->email);
        $admin->setFirstName($data->firstName);
        $admin->setLastName($data->lastName);
        
        // Hash du mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($admin, $data->password);
        $admin->setPassword($hashedPassword);
        
        // Ajouter le rôle ROLE_ADMIN
        $roles = ['ROLE_ADMIN'];
        if (!empty($data->roles)) {
            // Fusionner avec les rôles additionnels fournis
            $roles = array_unique(array_merge($roles, $data->roles));
        }
        $admin->setRoles($roles);
        
        $admin->setIsVerified($data->isVerified ?? false);

        $this->entityManager->persist($admin);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Administrateur créé avec succès',
            'id' => $admin->getId(),
            'administrator' => [
                'id' => $admin->getId(),
                'email' => $admin->getEmail(),
                'firstName' => $admin->getFirstName(),
                'lastName' => $admin->getLastName(),
                'roles' => $admin->getRoles(),
                'isVerified' => $admin->isVerified(),
                'createdAt' => $admin->getCreatedAt()?->format('c'),
            ]
        ], 201);
    }
}
