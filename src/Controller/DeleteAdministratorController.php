<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[AsController]
class DeleteAdministratorController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
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

        // Empêcher la suppression du dernier administrateur
        $allAdmins = $this->userRepository->findByRole('ROLE_ADMIN');
        if (count($allAdmins) <= 1) {
            return $this->json([
                'error' => 'Impossible de supprimer le dernier administrateur du système'
            ], 400);
        }

        $this->entityManager->remove($admin);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Administrateur supprimé avec succès'
        ]);
    }
}
