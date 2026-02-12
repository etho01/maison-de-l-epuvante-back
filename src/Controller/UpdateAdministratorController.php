<?php

namespace App\Controller;

use App\ApiResource\Administrator as AdministratorResource;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsController]
class UpdateAdministratorController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function __invoke(int $id, #[MapRequestPayload] AdministratorResource $data): JsonResponse
    {
        $admin = $this->userRepository->find($id);

        if (!$admin) {
            throw new NotFoundHttpException('Administrateur non trouvé');
        }

        // Vérifier que l'utilisateur est bien un administrateur
        if (!in_array('ROLE_ADMIN', $admin->getRoles())) {
            throw new NotFoundHttpException('Cet utilisateur n\'est pas un administrateur');
        }

        // Mise à jour des champs
        if ($data->email !== null) {
            $admin->setEmail($data->email);
        }

        if ($data->firstName !== null) {
            $admin->setFirstName($data->firstName);
        }

        if ($data->lastName !== null) {
            $admin->setLastName($data->lastName);
        }

        // Mise à jour du mot de passe si fourni
        if ($data->password !== null) {
            $hashedPassword = $this->passwordHasher->hashPassword($admin, $data->password);
            $admin->setPassword($hashedPassword);
        }

        // Mise à jour des rôles (toujours garder ROLE_ADMIN)
        if (!empty($data->roles)) {
            $roles = $data->roles;
            if (!in_array('ROLE_ADMIN', $roles)) {
                $roles[] = 'ROLE_ADMIN';
            }
            $admin->setRoles($roles);
        }

        if ($data->isVerified !== null) {
            $admin->setIsVerified($data->isVerified);
        }

        $admin->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->flush();

        return $this->json([
            'message' => 'Administrateur mis à jour avec succès',
            'administrator' => [
                'id' => $admin->getId(),
                'email' => $admin->getEmail(),
                'firstName' => $admin->getFirstName(),
                'lastName' => $admin->getLastName(),
                'roles' => $admin->getRoles(),
                'isVerified' => $admin->isVerified(),
                'updatedAt' => $admin->getUpdatedAt()?->format('c'),
            ]
        ]);
    }
}
