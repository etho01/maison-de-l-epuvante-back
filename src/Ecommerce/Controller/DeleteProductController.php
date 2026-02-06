<?php

namespace App\Ecommerce\Controller;

use App\Ecommerce\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class DeleteProductController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(Product $data): JsonResponse
    {
        $this->entityManager->remove($data);
        $this->entityManager->flush();

        return $this->json(['message' => 'Produit supprimé avec succès'], 204);
    }
}
