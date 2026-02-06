<?php

namespace App\Tests\Entity;

use App\Entity\Subscription;
use App\Entity\User;
use App\Ecommerce\Entity\Cart;
use App\Ecommerce\Entity\Order;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        $this->user = new User();
    }

    public function testNewUserHasCorrectDefaults(): void
    {
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->user->getCreatedAt());
        $this->assertFalse($this->user->isVerified());
        $this->assertEquals(['ROLE_USER'], $this->user->getRoles());
        $this->assertCount(0, $this->user->getOrders());
        $this->assertCount(0, $this->user->getSubscriptions());
    }

    public function testSetAndGetEmail(): void
    {
        $email = 'test@example.com';
        $this->user->setEmail($email);
        
        $this->assertEquals($email, $this->user->getEmail());
        $this->assertEquals($email, $this->user->getUserIdentifier());
    }

    public function testSetAndGetPassword(): void
    {
        $password = 'hashed_password';
        $this->user->setPassword($password);
        
        $this->assertEquals($password, $this->user->getPassword());
    }

    public function testSetAndGetPlainPassword(): void
    {
        $plainPassword = 'plain_password_123';
        $this->user->setPlainPassword($plainPassword);
        
        $this->assertEquals($plainPassword, $this->user->getPlainPassword());
    }

    public function testSetAndGetFirstName(): void
    {
        $firstName = 'John';
        $this->user->setFirstName($firstName);
        
        $this->assertEquals($firstName, $this->user->getFirstName());
    }

    public function testSetAndGetLastName(): void
    {
        $lastName = 'Doe';
        $this->user->setLastName($lastName);
        
        $this->assertEquals($lastName, $this->user->getLastName());
    }

    public function testGetFullName(): void
    {
        $this->user->setFirstName('John');
        $this->user->setLastName('Doe');
        
        $this->assertEquals('John Doe', $this->user->getFullName());
    }

    public function testGetFullNameWithOnlyFirstName(): void
    {
        $this->user->setFirstName('John');
        
        $this->assertEquals('John', $this->user->getFullName());
    }

    public function testGetFullNameWithOnlyLastName(): void
    {
        $this->user->setLastName('Doe');
        
        $this->assertEquals('Doe', $this->user->getFullName());
    }

    public function testGetFullNameWithNoName(): void
    {
        $this->assertEquals('', $this->user->getFullName());
    }

    public function testSetAndGetRoles(): void
    {
        $roles = ['ROLE_USER', 'ROLE_ADMIN'];
        $this->user->setRoles($roles);
        
        $this->assertEquals($roles, $this->user->getRoles());
    }

    public function testRolesAlwaysContainsRoleUser(): void
    {
        $this->user->setRoles(['ROLE_ADMIN']);
        
        $roles = $this->user->getRoles();
        $this->assertContains('ROLE_USER', $roles);
    }

    public function testSetIsVerified(): void
    {
        $this->assertFalse($this->user->isVerified());
        
        $this->user->setIsVerified(true);
        $this->assertTrue($this->user->isVerified());
        
        $this->user->setIsVerified(false);
        $this->assertFalse($this->user->isVerified());
    }

    public function testEraseCredentials(): void
    {
        $this->user->setPlainPassword('plain_password_123');
        $this->user->eraseCredentials();
        
        $this->assertNull($this->user->getPlainPassword());
    }

    public function testSetUpdatedAtOnUpdate(): void
    {
        $initialUpdatedAt = $this->user->getUpdatedAt();
        sleep(1);
        
        $this->user->setUpdatedAt(new \DateTimeImmutable());
        $updatedAt = $this->user->getUpdatedAt();
        
        $this->assertInstanceOf(\DateTimeImmutable::class, $updatedAt);
        $this->assertNotEquals($initialUpdatedAt, $updatedAt);
    }

    public function testSetAndGetCart(): void
    {
        $cart = $this->createMock(Cart::class);
        $this->user->setCart($cart);
        
        $this->assertEquals($cart, $this->user->getCart());
    }

    public function testAddAndRemoveOrder(): void
    {
        $order = $this->createMock(Order::class);
        $order->expects($this->once())
            ->method('setUser')
            ->with($this->user);
        
        $this->user->addOrder($order);
        $this->assertCount(1, $this->user->getOrders());
        
        $this->user->removeOrder($order);
        $this->assertCount(0, $this->user->getOrders());
    }

    public function testAddAndRemoveSubscription(): void
    {
        $subscription = $this->createMock(Subscription::class);
        $subscription->expects($this->once())
            ->method('setUser')
            ->with($this->user);
        
        $this->user->addSubscription($subscription);
        $this->assertCount(1, $this->user->getSubscriptions());
        
        $this->user->removeSubscription($subscription);
        $this->assertCount(0, $this->user->getSubscriptions());
    }
}
