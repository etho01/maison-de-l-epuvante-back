<?php

namespace App\Ecommerce\Controller;

use App\Ecommerce\ApiResource\Product as ProductResource;
use App\Ecommerce\Entity\Category;
use App\Ecommerce\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\String\Slugger\SluggerInterface;

#[AsController]
class CreateProductController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SluggerInterface $slugger
    ) {}

    public function __invoke(#[MapRequestPayload] ProductResource $data): JsonResponse
    {
        $product = new Product();
        $product->setName($data->name);
        $product->setDescription($data->description);
        
        // Generate slug if not provided
        if (!$data->slug) {
            $data->slug = strtolower($this->slugger->slug($data->name));
        }
        $product->setSlug($data->slug);
        
        $product->setPrice((string) $data->price);
        $product->setStock($data->stock);
        $product->setType($data->type);
        $product->setSku($data->sku);
        $product->setImages($data->images ?? []);
        $product->setActive($data->active);
        $product->setExclusiveOnline($data->exclusiveOnline);
        $product->setWeight($data->weight ? (string) $data->weight : null);
        $product->setMetadata($data->metadata ?? []);

        if ($data->categoryId) {
            $category = $this->entityManager->getRepository(Category::class)->find($data->categoryId);
            if ($category) {
                $product->setCategory($category);
            }
        }

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Produit créé avec succès',
            'id' => $product->getId(),
            'product' => [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'slug' => $product->getSlug(),
                'price' => $product->getPrice(),
                'stock' => $product->getStock(),
                'type' => $product->getType()
            ]
        ], 201);
    }
}
