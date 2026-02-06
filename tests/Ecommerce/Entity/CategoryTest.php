<?php

namespace App\Tests\Ecommerce\Entity;

use App\Ecommerce\Entity\Category;
use App\Ecommerce\Entity\Product;
use PHPUnit\Framework\TestCase;

class CategoryTest extends TestCase
{
    private Category $category;

    protected function setUp(): void
    {
        $this->category = new Category();
    }

    public function testNewCategoryHasCorrectDefaults(): void
    {
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->category->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->category->getUpdatedAt());
        $this->assertCount(0, $this->category->getChildren());
        $this->assertCount(0, $this->category->getProducts());
    }

    public function testSetAndGetName(): void
    {
        $name = 'Horreur';
        $this->category->setName($name);
        
        $this->assertEquals($name, $this->category->getName());
    }

    public function testSetAndGetDescription(): void
    {
        $description = 'Catégorie de livres d\'horreur';
        $this->category->setDescription($description);
        
        $this->assertEquals($description, $this->category->getDescription());
    }

    public function testSetAndGetSlug(): void
    {
        $slug = 'horreur';
        $this->category->setSlug($slug);
        
        $this->assertEquals($slug, $this->category->getSlug());
    }

    public function testSetAndGetParent(): void
    {
        $parent = new Category();
        $parent->setName('Parent Category');
        
        $this->category->setParent($parent);
        
        $this->assertEquals($parent, $this->category->getParent());
    }

    public function testAddAndRemoveChild(): void
    {
        $child = $this->createMock(Category::class);
        $child->expects($this->once())
            ->method('setParent')
            ->with($this->category);
        
        $this->category->addChild($child);
        $this->assertCount(1, $this->category->getChildren());
        
        $this->category->removeChild($child);
        $this->assertCount(0, $this->category->getChildren());
    }

    public function testAddAndRemoveProduct(): void
    {
        $product = $this->createMock(Product::class);
        $product->expects($this->once())
            ->method('setCategory')
            ->with($this->category);
        
        $this->category->addProduct($product);
        $this->assertCount(1, $this->category->getProducts());
        
        $this->category->removeProduct($product);
        $this->assertCount(0, $this->category->getProducts());
    }

    public function testHasParent(): void
    {
        $this->assertFalse($this->category->hasParent());
        
        $parent = new Category();
        $this->category->setParent($parent);
        
        $this->assertTrue($this->category->hasParent());
    }

    public function testHasChildren(): void
    {
        $this->assertFalse($this->category->hasChildren());
        
        $child = new Category();
        $child->setParent($this->category);
        $this->category->addChild($child);
        
        $this->assertTrue($this->category->hasChildren());
    }

    public function testSetUpdatedAt(): void
    {
        $initialUpdatedAt = $this->category->getUpdatedAt();
        sleep(1);
        
        $this->category->setUpdatedAt(new \DateTimeImmutable());
        
        $this->assertNotEquals($initialUpdatedAt, $this->category->getUpdatedAt());
    }
}
