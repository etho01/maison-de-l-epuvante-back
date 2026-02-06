<?php

namespace App\Ecommerce\Controller;

use App\Ecommerce\ApiResource\Order as OrderResource;
use App\Ecommerce\Entity\Order;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Bundle\SecurityBundle\Security;

#[AsController]
class CreateOrderController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security
    ) {}

    public function __invoke(#[MapRequestPayload] OrderResource $data): JsonResponse
    {
        $user = $this->security->getUser();
        
        if (!$user instanceof User) {
            return $this->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        $order = new Order();
        $order->setUser($user);
        $order->setStatus($data->status);
        $order->setShippingAddress($data->shippingAddress);
        $order->setBillingAddress($data->billingAddress);
        $order->setPaymentMethod($data->paymentMethod);
        $order->setPaymentIntentId($data->paymentIntentId);

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Commande créée avec succès',
            'id' => $order->getId(),
            'order' => [
                'id' => $order->getId(),
                'status' => $order->getStatus(),
                'totalAmount' => $order->getTotalAmount()
            ]
        ], 201);
    }
}
