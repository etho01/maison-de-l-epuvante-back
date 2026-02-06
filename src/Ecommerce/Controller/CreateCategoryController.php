<?php

namespace App\Ecommerce\Controller;

use App\Ecommerce\ApiResource\Category as CategoryResource;
use App\Ecommerce\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\String\Slugger\SluggerInterface;

#[AsController]
class CreateCategoryController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SluggerInterface $slugger
    ) {}

    public function __invoke(#[MapRequestPayload] CategoryResource $data): JsonResponse
    {
        $category = new Category();
        $category->setName($data->name);
        $category->setDescription($data->description);
        
        // Generate slug if not provided
        if (!$data->slug) {
            $data->slug = strtolower($this->slugger->slug($data->name));
        }
        $category->setSlug($data->slug);

        if ($data->parentId) {
            $parent = $this->entityManager->getRepository(Category::class)->find($data->parentId);
            if ($parent) {
                $category->setParent($parent);
            }
        }

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Catégorie créée avec succès',
            'id' => $category->getId(),
            'category' => [
                'id' => $category->getId(),
                'name' => $category->getName(),
                'slug' => $category->getSlug(),
                'description' => $category->getDescription()
            ]
        ], 201);
    }
}
