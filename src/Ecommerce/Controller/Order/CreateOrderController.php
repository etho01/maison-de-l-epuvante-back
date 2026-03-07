<?php

namespace App\Ecommerce\Controller\Order;

use App\Enum\ApiError;
use App\Trait\ApiResponseTrait;
use App\Ecommerce\Dto\OrderCheckoutInput;
use App\Ecommerce\Entity\OrderItem;
use App\Ecommerce\Entity\Delivery;
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
    use ApiResponseTrait;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security,
        private ProductRepository $productRepository,
        private StripeClient $stripeClient,
        #[\Symfony\Component\DependencyInjection\Attribute\Autowire('%env(FRONTEND_URL)%')]
        private string $defaultUri
    ) {}

    public function __invoke(#[MapRequestPayload] OrderCheckoutInput $data): JsonResponse
    {
        $user = $this->security->getUser();
        
        if (!$user instanceof User) {
            return $this->errorResponse(401, ApiError::USER_NOT_AUTHENTICATED);
        }

        $order = new Order();
        $order->setUser($user);
        $order->setBillingAddress($data->billingAddress);
        $order->setPaymentMethod($data->paymentMethod);
        $order->setCustomerNotes($data->customerNotes);

        $totalAmount = 0.0;

        foreach ($data->products as $productLine) {
            $productId = (int) $productLine['id'];
            $quantity = (int) $productLine['quantity'];

            $product = $this->productRepository->find($productId);

            if (!$product) {
                return $this->errorResponse(404, ApiError::PRODUCT_NOT_FOUND, [
                    'productId' => $productId,
                    'productName' => $productLine['name'] ?? null
                ]);
            }

            if (($quantity <= 0 || $quantity > $product->getStock()) && ($product->getType() === 'digital' && $product->getStock() != -1)) { // stock -1 = product infinite
                return $this->errorResponse(400, ApiError::INVALID_QUANTITY, [
                    'productId' => $productId,
                    'quantity' => $quantity,
                    'availableStock' => $product->getStock(),
                    'productName' => $product->getName()
                ]);
            }

            $unitPrice = (float) $product->getPrice();
            $lineTotal = $unitPrice * $quantity;
            $totalAmount += $lineTotal;

            $product->setStock($product->getStock() - $quantity);
            $this->entityManager->persist($product);
            $this->entityManager->flush();

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

        // Créer une livraison uniquement si la commande contient des produits physiques
        if ($order->hasPhysicalProducts()) {
            $delivery = new Delivery();
            $delivery->setShippingAddress($data->shippingAddress);
            $order->setDelivery($delivery);
            
            // Lier les items physiques à la livraison
            foreach ($order->getItems() as $orderItem) {
                if ($orderItem->getProduct() && $orderItem->getProduct()->getType() === 'physical') {
                    $delivery->addItem($orderItem);
                }
            }
        }

        // Préparer les line_items pour Stripe Checkout
        $lineItems = [];
        foreach ($order->getItems() as $orderItem) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $orderItem->getProductName(),
                        'description' => $orderItem->getProductSku(),
                    ],
                    'unit_amount' => (int) round((float) $orderItem->getUnitPrice() * 100),
                ],
                'quantity' => $orderItem->getQuantity(),
            ];
        }

        $connection = $this->entityManager->getConnection();
        $checkoutSessionId = null;
        $checkoutUrl = null;
        $connection->beginTransaction();

        try {
            $this->entityManager->persist($order);
            $this->entityManager->flush();

            $checkoutSession = $this->stripeClient->checkout->sessions->create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => $this->defaultUri . '/success?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $this->defaultUri . '/cancel?session_id={CHECKOUT_SESSION_ID}',
                'metadata' => [
                    'orderId' => $order->getId(),
                    'orderNumber' => $order->getOrderNumber(),
                ],
                'customer_email' => $user->getEmail(),
            ]);

            $checkoutSessionId = $checkoutSession->id;
            $checkoutUrl = $checkoutSession->url;
            $order->setPaymentIntentId($checkoutSessionId);
            $this->entityManager->flush();

            $connection->commit();
        } catch (ApiErrorException | Throwable $exception) {
            $connection->rollBack();

            return $this->errorResponse(502, ApiError::PAYMENT_ERROR);
        }

        return $this->successResponse([
            'id' => $order->getId(),
            'order' => [
                'id' => $order->getId(),
                'orderNumber' => $order->getOrderNumber(),
                'status' => $order->getStatus(),
                'totalAmount' => $order->getTotalAmount()
            ],
            'stripeCheckout' => [
                'sessionId' => $checkoutSessionId,
                'url' => $checkoutUrl
            ]
        ], 201);
    }
}
