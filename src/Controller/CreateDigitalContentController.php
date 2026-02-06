<?php

namespace App\Controller;

use App\ApiResource\DigitalContent as DigitalContentResource;
use App\Ecommerce\Entity\Product;
use App\Entity\DigitalContent;
use App\Entity\SubscriptionPlan;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

#[AsController]
class CreateDigitalContentController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function __invoke(#[MapRequestPayload] DigitalContentResource $data): JsonResponse
    {
        $content = new DigitalContent();
        $content->setName($data->name);
        $content->setDescription($data->description);
        $content->setFilePath($data->filePath);
        $content->setContentType($data->contentType);
        $content->setFileSize($data->fileSize);
        $content->setRequiresSubscription($data->requiresSubscription);

        if ($data->productId) {
            $product = $this->entityManager->getRepository(Product::class)->find($data->productId);
            if ($product) {
                $content->setProduct($product);
            }
        }

        if ($data->subscriptionPlanId) {
            $plan = $this->entityManager->getRepository(SubscriptionPlan::class)->find($data->subscriptionPlanId);
            if ($plan) {
                $content->setSubscriptionPlan($plan);
            }
        }

        $this->entityManager->persist($content);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Contenu numérique créé avec succès',
            'id' => $content->getId(),
            'content' => [
                'id' => $content->getId(),
                'name' => $content->getName(),
                'contentType' => $content->getContentType(),
                'fileSize' => $content->getFileSize()
            ]
        ], 201);
    }
}
