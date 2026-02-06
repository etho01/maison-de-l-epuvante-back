<?php

namespace App\Tests\Ecommerce\Entity;

use App\Ecommerce\Entity\Cart;
use App\Ecommerce\Entity\CartItem;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class CartTest extends TestCase
{
    private Cart $cart;

    protected function setUp(): void
    {
        $this->cart = new Cart();
    }

    public function testNewCartHasCorrectDefaults(): void
    {
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->cart->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->cart->getUpdatedAt());
        $this->assertCount(0, $this->cart->getItems());
    }

    public function testSetAndGetUser(): void
    {
        $user = $this->createMock(User::class);
        $this->cart->setUser($user);
        
        $this->assertEquals($user, $this->cart->getUser());
    }

    public function testAddAndRemoveItem(): void
    {
        $item = $this->createMock(CartItem::class);
        $item->method('setCart')
            ->willReturn($item);
        $item->method('getCart')
            ->willReturn($this->cart);
        
        $this->cart->addItem($item);
        $this->assertCount(1, $this->cart->getItems());
        
        $this->cart->removeItem($item);
        $this->assertCount(0, $this->cart->getItems());
    }

    public function testGetTotalWithEmptyCart(): void
    {
        $total = $this->cart->getTotal();
        
        $this->assertEquals(0, $total);
    }

    public function testClearCart(): void
    {
        $item1 = $this->createMock(CartItem::class);
        $item1->method('setCart')->willReturn($item1);
        
        $item2 = $this->createMock(CartItem::class);
        $item2->method('setCart')->willReturn($item2);
        
        $this->cart->addItem($item1);
        $this->cart->addItem($item2);
        
        $this->assertCount(2, $this->cart->getItems());
        
        $this->cart->clear();
        
        $this->assertCount(0, $this->cart->getItems());
    }

    public function testGetItemsCount(): void
    {
        $this->assertEquals(0, $this->cart->getItemsCount());
        
        $item1 = $this->createMock(CartItem::class);
        $item1->method('setCart')->willReturn($item1);
        $item1->method('getQuantity')->willReturn(2);
        
        $item2 = $this->createMock(CartItem::class);
        $item2->method('setCart')->willReturn($item2);
        $item2->method('getQuantity')->willReturn(3);
        
        $this->cart->addItem($item1);
        $this->cart->addItem($item2);
        
        $this->assertEquals(2, $this->cart->getItemsCount());
    }

    public function testIsEmpty(): void
    {
        $this->assertTrue($this->cart->isEmpty());
        
        $item = $this->createMock(CartItem::class);
        $item->method('setCart')->willReturn($item);
        
        $this->cart->addItem($item);
        
        $this->assertFalse($this->cart->isEmpty());
    }

    public function testSetUpdatedAt(): void
    {
        $initialUpdatedAt = $this->cart->getUpdatedAt();
        sleep(1);
        
        $this->cart->setUpdatedAt(new \DateTimeImmutable());
        
        $this->assertNotEquals($initialUpdatedAt, $this->cart->getUpdatedAt());
    }
}
