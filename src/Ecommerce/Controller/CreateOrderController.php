<?php

namespace App\Ecommerce\Controller;

use App\Ecommerce\Dto\OrderCheckoutInput;
use App\Ecommerce\Entity\OrderItem;
use App\Ecommerce\Repository\ProductRepository;
use App\Ecommerce\Entity\Order;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Bundle\SecurityBundle\Security;
use Throwable;

#[AsController]
class CreateOrderController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security,
        private ProductRepository $productRepository,
        private StripeClient $stripeClient
    ) {}

    public function __invoke(#[MapRequestPayload] OrderCheckoutInput $data): JsonResponse
    {
        $user = $this->security->getUser();
        
        if (!$user instanceof User) {
            return $this->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        $order = new Order();
        $order->setUser($user);
        $order->setShippingAddress($data->shippingAddress);
        $order->setBillingAddress($data->billingAddress);
        $order->setPaymentMethod($data->paymentMethod);
        $order->setCustomerNotes($data->customerNotes);

        $totalAmount = 0.0;

        foreach ($data->products as $productLine) {
            $productId = (int) $productLine['id'];
            $quantity = (int) $productLine['quantity'];

            if ($quantity <= 0) {
                return $this->json(['error' => 'La quantité doit être supérieure à 0'], 400);
            }

            $product = $this->productRepository->find($productId);

            if (!$product) {
                return $this->json(['error' => sprintf('Produit %d introuvable', $productId)], 404);
            }

            $unitPrice = (float) $product->getPrice();
            $lineTotal = $unitPrice * $quantity;
            $totalAmount += $lineTotal;

            $orderItem = new OrderItem();
            $orderItem->setProduct($product);
            $orderItem->setQuantity($quantity);
            $orderItem->setUnitPrice(number_format($unitPrice, 2, '.', ''));
            $orderItem->setTotalPrice(number_format($lineTotal, 2, '.', ''));
            $orderItem->setProductName($product->getName() ?? $productLine['name']);
            $orderItem->setProductSku($product->getSku());

            $order->addItem($orderItem);
        }

        $order->setTotalAmount(number_format($totalAmount, 2, '.', ''));

        $connection = $this->entityManager->getConnection();
        $paymentIntentId = null;
        $paymentIntentClientSecret = null;
        $connection->beginTransaction();

        try {
            $this->entityManager->persist($order);
            $this->entityManager->flush();

            $amountCents = (int) round($totalAmount * 100);

            $paymentIntent = $this->stripeClient->paymentIntents->create([
                'amount' => $amountCents,
                'currency' => 'eur',
                'description' => sprintf('Commande %s', $order->getOrderNumber()),
                'metadata' => [
                    'orderId' => $order->getId(),
                    'orderNumber' => $order->getOrderNumber(),
                ],
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            $paymentIntentId = $paymentIntent->id;
            $paymentIntentClientSecret = $paymentIntent->client_secret;
            $order->setPaymentIntentId($paymentIntentId);
            $this->entityManager->flush();

            $connection->commit();
        } catch (ApiErrorException | Throwable $exception) {
            $connection->rollBack();

            return $this->json([
                'error' => 'Erreur lors de la création du paiement Stripe',
                'details' => $exception->getMessage(),
            ], 502);
        }

        return $this->json([
            'message' => 'Commande créée avec succès',
            'id' => $order->getId(),
            'order' => [
                'id' => $order->getId(),
                'orderNumber' => $order->getOrderNumber(),
                'status' => $order->getStatus(),
                'totalAmount' => $order->getTotalAmount()
            ],
            'stripePayment' => [
                'paymentIntentId' => $paymentIntentId,
                'clientSecret' => $paymentIntentClientSecret
            ]
        ], 201);
    }
}
