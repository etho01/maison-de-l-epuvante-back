<?php

namespace App\Ecommerce\Controller;

use App\Ecommerce\ApiResource\Category as CategoryResource;
use App\Ecommerce\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[AsController]
class UpdateCategoryController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function __invoke(int $id, #[MapRequestPayload] CategoryResource $data): JsonResponse
    {
        $category = $this->entityManager->getRepository(Category::class)->find($id);
        
        if (!$category) {
            throw new NotFoundHttpException('Catégorie non trouvée');
        }

        if ($data->name) {
            $category->setName($data->name);
        }
        
        if ($data->description !== null) {
            $category->setDescription($data->description);
        }
        
        if ($data->slug) {
            $category->setSlug($data->slug);
        }

        if ($data->parentId !== null) {
            if ($data->parentId) {
                $parent = $this->entityManager->getRepository(Category::class)->find($data->parentId);
                if ($parent) {
                    $category->setParent($parent);
                }
            } else {
                $category->setParent(null);
            }
        }

        $category->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Catégorie mise à jour avec succès',
            'category' => [
                'id' => $category->getId(),
                'name' => $category->getName(),
                'slug' => $category->getSlug(),
                'description' => $category->getDescription()
            ]
        ]);
    }
}
