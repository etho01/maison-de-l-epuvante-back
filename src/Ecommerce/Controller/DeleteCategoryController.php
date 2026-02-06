<?php

namespace App\Ecommerce\Controller;

use App\Ecommerce\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class DeleteCategoryController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(Category $data): JsonResponse
    {
        $this->entityManager->remove($data);
        $this->entityManager->flush();

        return $this->json(['message' => 'Catégorie supprimée avec succès'], 204);
    }
}
