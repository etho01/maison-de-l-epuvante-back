<?php

namespace App\Ecommerce\Controller;

use App\Enum\ApiError;
use App\Trait\ApiResponseTrait;
use App\Ecommerce\ApiResource\Product as ProductResource;
use App\Ecommerce\Entity\Category;
use App\Ecommerce\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

#[AsController]
class UpdateProductController extends AbstractController
{
    use ApiResponseTrait;

    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function __invoke(int $id, #[MapRequestPayload] ProductResource $data): JsonResponse
    {
        $product = $this->entityManager->getRepository(Product::class)->find($id);
        
        if (!$product) {
            return $this->errorResponse(404, ApiError::PRODUCT_NOT_FOUND);
        }

        if ($data->name) {
            $product->setName($data->name);
        }
        
        if ($data->description !== null) {
            $product->setDescription($data->description);
        }
        
        if ($data->slug) {
            $product->setSlug($data->slug);
        }
        
        if ($data->price !== null) {
            $product->setPrice((string) $data->price);
        }
        
        if ($data->stock !== null) {
            $product->setStock($data->stock);
        }
        
        if ($data->type) {
            $product->setType($data->type);
        }
        
        if ($data->sku !== null) {
            $product->setSku($data->sku);
        }
        
        if ($data->images !== null) {
            $product->setImages($data->images);
        }
        
        if ($data->active !== null) {
            $product->setActive($data->active);
        }
        
        if ($data->exclusiveOnline !== null) {
            $product->setExclusiveOnline($data->exclusiveOnline);
        }
        
        if ($data->weight !== null) {
            $product->setWeight($data->weight ? (string) $data->weight : null);
        }
        
        if ($data->metadata !== null) {
            $product->setMetadata($data->metadata);
        }

        if ($data->categoryId !== null) {
            if ($data->categoryId) {
                $category = $this->entityManager->getRepository(Category::class)->find($data->categoryId);
                if ($category) {
                    $product->setCategory($category);
                }
            } else {
                $product->setCategory(null);
            }
        }

        $product->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->flush();

        return $this->successResponse([
            'id' => $product->getId(),
            'name' => $product->getName(),
            'slug' => $product->getSlug(),
            'price' => $product->getPrice(),
            'stock' => $product->getStock()
        ]);
    }
}
