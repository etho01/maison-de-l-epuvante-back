<?php

namespace App\Tests\Ecommerce\Entity;

use App\Ecommerce\Entity\Order;
use App\Ecommerce\Entity\OrderItem;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class OrderTest extends TestCase
{
    private Order $order;

    protected function setUp(): void
    {
        $this->order = new Order();
    }

    public function testNewOrderHasCorrectDefaults(): void
    {
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->order->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->order->getUpdatedAt());
        $this->assertEquals('pending', $this->order->getStatus());
        $this->assertEquals('0.00', $this->order->getTotalAmount());
        $this->assertEquals('0.00', $this->order->getShippingCost());
        $this->assertEquals('0.00', $this->order->getTaxAmount());
        $this->assertCount(0, $this->order->getItems());
        $this->assertNotNull($this->order->getOrderNumber());
        $this->assertStringStartsWith('ORD-', $this->order->getOrderNumber());
    }

    public function testSetAndGetUser(): void
    {
        $user = $this->createMock(User::class);
        $this->order->setUser($user);
        
        $this->assertEquals($user, $this->order->getUser());
    }

    public function testSetAndGetStatus(): void
    {
        $statuses = ['pending', 'processing', 'paid', 'shipped', 'delivered', 'cancelled', 'refunded'];
        
        foreach ($statuses as $status) {
            $this->order->setStatus($status);
            $this->assertEquals($status, $this->order->getStatus());
        }
    }

    public function testSetAndGetTotalAmount(): void
    {
        $totalAmount = '99.99';
        $this->order->setTotalAmount($totalAmount);
        
        $this->assertEquals($totalAmount, $this->order->getTotalAmount());
    }

    public function testSetAndGetShippingCost(): void
    {
        $shippingCost = '5.99';
        $this->order->setShippingCost($shippingCost);
        
        $this->assertEquals($shippingCost, $this->order->getShippingCost());
    }

    public function testSetAndGetTaxAmount(): void
    {
        $taxAmount = '20.00';
        $this->order->setTaxAmount($taxAmount);
        
        $this->assertEquals($taxAmount, $this->order->getTaxAmount());
    }

    public function testSetAndGetShippingAddress(): void
    {
        $address = [
            'street' => '123 Main St',
            'city' => 'Paris',
            'postalCode' => '75001',
            'country' => 'France'
        ];
        $this->order->setShippingAddress($address);
        
        $this->assertEquals($address, $this->order->getShippingAddress());
    }

    public function testSetAndGetBillingAddress(): void
    {
        $address = [
            'street' => '456 Avenue',
            'city' => 'Lyon',
            'postalCode' => '69001',
            'country' => 'France'
        ];
        $this->order->setBillingAddress($address);
        
        $this->assertEquals($address, $this->order->getBillingAddress());
    }

    public function testSetAndGetPaymentMethod(): void
    {
        $paymentMethod = 'credit_card';
        $this->order->setPaymentMethod($paymentMethod);
        
        $this->assertEquals($paymentMethod, $this->order->getPaymentMethod());
    }

    public function testSetAndGetPaymentIntentId(): void
    {
        $paymentIntentId = 'pi_test_12345';
        $this->order->setPaymentIntentId($paymentIntentId);
        
        $this->assertEquals($paymentIntentId, $this->order->getPaymentIntentId());
    }

    public function testAddAndRemoveItem(): void
    {
        $item = $this->createMock(OrderItem::class);
        $item->expects($this->once())
            ->method('setOrder')
            ->with($this->order);
        
        $this->order->addItem($item);
        $this->assertCount(1, $this->order->getItems());
        
        $this->order->removeItem($item);
        $this->assertCount(0, $this->order->getItems());
    }

    public function testIsPending(): void
    {
        $this->order->setStatus('pending');
        $this->assertTrue($this->order->isPending());
        
        $this->order->setStatus('paid');
        $this->assertFalse($this->order->isPending());
    }

    public function testIsPaid(): void
    {
        $this->order->setStatus('paid');
        $this->assertTrue($this->order->isPaid());
        
        $this->order->setStatus('pending');
        $this->assertFalse($this->order->isPaid());
    }

    public function testIsShipped(): void
    {
        $this->order->setStatus('shipped');
        $this->assertTrue($this->order->isShipped());
        
        $this->order->setStatus('pending');
        $this->assertFalse($this->order->isShipped());
    }

    public function testIsDelivered(): void
    {
        $this->order->setStatus('delivered');
        $this->assertTrue($this->order->isDelivered());
        
        $this->order->setStatus('shipped');
        $this->assertFalse($this->order->isDelivered());
    }

    public function testIsCancelled(): void
    {
        $this->order->setStatus('cancelled');
        $this->assertTrue($this->order->isCancelled());
        
        $this->order->setStatus('pending');
        $this->assertFalse($this->order->isCancelled());
    }

    public function testMarkAsPaid(): void
    {
        $this->order->markAsPaid();
        
        $this->assertEquals('paid', $this->order->getStatus());
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->order->getPaidAt());
    }

    public function testMarkAsShipped(): void
    {
        $this->order->markAsShipped();
        
        $this->assertEquals('shipped', $this->order->getStatus());
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->order->getShippedAt());
    }

    public function testMarkAsDelivered(): void
    {
        $this->order->markAsDelivered();
        
        $this->assertEquals('delivered', $this->order->getStatus());
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->order->getDeliveredAt());
    }

    public function testCancel(): void
    {
        $this->order->cancel();
        
        $this->assertEquals('cancelled', $this->order->getStatus());
    }

    public function testSetUpdatedAt(): void
    {
        $initialUpdatedAt = $this->order->getUpdatedAt();
        sleep(1);
        
        $this->order->setUpdatedAt(new \DateTimeImmutable());
        
        $this->assertNotEquals($initialUpdatedAt, $this->order->getUpdatedAt());
    }
}
