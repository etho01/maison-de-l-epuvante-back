<?php

namespace App\Tests\Ecommerce\Entity;

use App\Ecommerce\Entity\CartItem;
use App\Ecommerce\Entity\Category;
use App\Ecommerce\Entity\OrderItem;
use App\Ecommerce\Entity\Product;
use App\Entity\DigitalContent;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    private Product $product;

    protected function setUp(): void
    {
        $this->product = new Product();
    }

    public function testNewProductHasCorrectDefaults(): void
    {
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->product->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->product->getUpdatedAt());
        $this->assertEquals(0, $this->product->getStock());
        $this->assertEquals('physical', $this->product->getType());
        $this->assertTrue($this->product->isActive());
        $this->assertFalse($this->product->isExclusiveOnline());
        $this->assertEquals([], $this->product->getImages());
        $this->assertEquals([], $this->product->getMetadata());
        $this->assertCount(0, $this->product->getOrderItems());
        $this->assertCount(0, $this->product->getCartItems());
    }

    public function testSetAndGetName(): void
    {
        $name = 'La Maison de l\'Épouvante';
        $this->product->setName($name);
        
        $this->assertEquals($name, $this->product->getName());
    }

    public function testSetAndGetDescription(): void
    {
        $description = 'Un livre terrifiant';
        $this->product->setDescription($description);
        
        $this->assertEquals($description, $this->product->getDescription());
    }

    public function testSetAndGetSlug(): void
    {
        $slug = 'la-maison-de-lepouvante';
        $this->product->setSlug($slug);
        
        $this->assertEquals($slug, $this->product->getSlug());
    }

    public function testSetAndGetPrice(): void
    {
        $price = '19.99';
        $this->product->setPrice($price);
        
        $this->assertEquals($price, $this->product->getPrice());
    }

    public function testSetAndGetStock(): void
    {
        $stock = 50;
        $this->product->setStock($stock);
        
        $this->assertEquals($stock, $this->product->getStock());
    }

    public function testSetAndGetType(): void
    {
        $types = ['physical', 'digital', 'subscription'];
        
        foreach ($types as $type) {
            $this->product->setType($type);
            $this->assertEquals($type, $this->product->getType());
        }
    }

    public function testSetAndGetSku(): void
    {
        $sku = 'BOOK-001';
        $this->product->setSku($sku);
        
        $this->assertEquals($sku, $this->product->getSku());
    }

    public function testSetAndGetImages(): void
    {
        $images = [
            '/images/product1.jpg',
            '/images/product2.jpg'
        ];
        $this->product->setImages($images);
        
        $this->assertEquals($images, $this->product->getImages());
    }

    public function testSetAndGetActive(): void
    {
        $this->product->setActive(false);
        $this->assertFalse($this->product->isActive());
        
        $this->product->setActive(true);
        $this->assertTrue($this->product->isActive());
    }

    public function testSetAndGetExclusiveOnline(): void
    {
        $this->product->setExclusiveOnline(true);
        $this->assertTrue($this->product->isExclusiveOnline());
        
        $this->product->setExclusiveOnline(false);
        $this->assertFalse($this->product->isExclusiveOnline());
    }

    public function testSetAndGetCategory(): void
    {
        $category = new Category();
        $category->setName('Horreur');
        
        $this->product->setCategory($category);
        
        $this->assertEquals($category, $this->product->getCategory());
    }

    public function testSetAndGetWeight(): void
    {
        $weight = '0.5';
        $this->product->setWeight($weight);
        
        $this->assertEquals($weight, $this->product->getWeight());
    }

    public function testSetAndGetMetadata(): void
    {
        $metadata = ['author' => 'H.P. Lovecraft', 'pages' => 200];
        $this->product->setMetadata($metadata);
        
        $this->assertEquals($metadata, $this->product->getMetadata());
    }

    public function testSetAndGetDigitalContent(): void
    {
        $digitalContent = $this->createMock(DigitalContent::class);
        $this->product->setDigitalContent($digitalContent);
        
        $this->assertEquals($digitalContent, $this->product->getDigitalContent());
    }

    public function testAddAndRemoveOrderItem(): void
    {
        $orderItem = $this->createMock(OrderItem::class);
        $orderItem->expects($this->once())
            ->method('setProduct')
            ->with($this->product);
        
        $this->product->addOrderItem($orderItem);
        $this->assertCount(1, $this->product->getOrderItems());
        
        $this->product->removeOrderItem($orderItem);
        $this->assertCount(0, $this->product->getOrderItems());
    }

    public function testAddAndRemoveCartItem(): void
    {
        $cartItem = $this->createMock(CartItem::class);
        $cartItem->expects($this->once())
            ->method('setProduct')
            ->with($this->product);
        
        $this->product->addCartItem($cartItem);
        $this->assertCount(1, $this->product->getCartItems());
        
        $this->product->removeCartItem($cartItem);
        $this->assertCount(0, $this->product->getCartItems());
    }

    public function testIsPhysical(): void
    {
        $this->product->setType('physical');
        $this->assertTrue($this->product->isPhysical());
        
        $this->product->setType('digital');
        $this->assertFalse($this->product->isPhysical());
    }

    public function testIsDigital(): void
    {
        $this->product->setType('digital');
        $this->assertTrue($this->product->isDigital());
        
        $this->product->setType('physical');
        $this->assertFalse($this->product->isDigital());
    }

    public function testIsSubscription(): void
    {
        $this->product->setType('subscription');
        $this->assertTrue($this->product->isSubscription());
        
        $this->product->setType('physical');
        $this->assertFalse($this->product->isSubscription());
    }

    public function testIsInStock(): void
    {
        $this->product->setStock(0);
        $this->assertFalse($this->product->isInStock());
        
        $this->product->setStock(10);
        $this->assertTrue($this->product->isInStock());
    }

    public function testDecreaseStock(): void
    {
        $this->product->setStock(10);
        $this->product->decreaseStock(3);
        
        $this->assertEquals(7, $this->product->getStock());
    }

    public function testIncreaseStock(): void
    {
        $this->product->setStock(10);
        $this->product->increaseStock(5);
        
        $this->assertEquals(15, $this->product->getStock());
    }

    public function testSetUpdatedAt(): void
    {
        $initialUpdatedAt = $this->product->getUpdatedAt();
        sleep(1);
        
        $this->product->setUpdatedAt(new \DateTimeImmutable());
        
        $this->assertNotEquals($initialUpdatedAt, $this->product->getUpdatedAt());
    }
}
