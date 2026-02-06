<?php

namespace App\Tests\Entity;

use App\Entity\Subscription;
use App\Entity\SubscriptionPlan;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class SubscriptionTest extends TestCase
{
    private Subscription $subscription;

    protected function setUp(): void
    {
        $this->subscription = new Subscription();
    }

    public function testNewSubscriptionHasCorrectDefaults(): void
    {
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->subscription->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->subscription->getUpdatedAt());
        $this->assertEquals('pending', $this->subscription->getStatus());
        $this->assertTrue($this->subscription->isAutoRenew());
    }

    public function testSetAndGetUser(): void
    {
        $user = $this->createMock(User::class);
        $this->subscription->setUser($user);
        
        $this->assertEquals($user, $this->subscription->getUser());
    }

    public function testSetAndGetPlan(): void
    {
        $plan = $this->createMock(SubscriptionPlan::class);
        $this->subscription->setPlan($plan);
        
        $this->assertEquals($plan, $this->subscription->getPlan());
    }

    public function testSetAndGetStatus(): void
    {
        $statuses = ['active', 'cancelled', 'expired', 'pending'];
        
        foreach ($statuses as $status) {
            $this->subscription->setStatus($status);
            $this->assertEquals($status, $this->subscription->getStatus());
        }
    }

    public function testSetAndGetStartDate(): void
    {
        $startDate = new \DateTimeImmutable('2026-01-01');
        $this->subscription->setStartDate($startDate);
        
        $this->assertEquals($startDate, $this->subscription->getStartDate());
    }

    public function testSetAndGetEndDate(): void
    {
        $endDate = new \DateTimeImmutable('2026-12-31');
        $this->subscription->setEndDate($endDate);
        
        $this->assertEquals($endDate, $this->subscription->getEndDate());
    }

    public function testSetAndGetAutoRenew(): void
    {
        $this->subscription->setAutoRenew(false);
        $this->assertFalse($this->subscription->isAutoRenew());
        
        $this->subscription->setAutoRenew(true);
        $this->assertTrue($this->subscription->isAutoRenew());
    }

    public function testSetAndGetCancelledAt(): void
    {
        $cancelledAt = new \DateTimeImmutable('2026-06-01');
        $this->subscription->setCancelledAt($cancelledAt);
        
        $this->assertEquals($cancelledAt, $this->subscription->getCancelledAt());
    }

    public function testSetAndGetPaymentIntentId(): void
    {
        $paymentIntentId = 'pi_test_123456';
        $this->subscription->setPaymentIntentId($paymentIntentId);
        
        $this->assertEquals($paymentIntentId, $this->subscription->getPaymentIntentId());
    }

    public function testIsActive(): void
    {
        $this->subscription->setStatus('active');
        $this->subscription->setEndDate(new \DateTimeImmutable('+1 month'));
        $this->assertTrue($this->subscription->isActive());
        
        $this->subscription->setStatus('cancelled');
        $this->assertFalse($this->subscription->isActive());
        
        $this->subscription->setStatus('expired');
        $this->assertFalse($this->subscription->isActive());
        
        $this->subscription->setStatus('pending');
        $this->assertFalse($this->subscription->isActive());
    }

    public function testIsExpired(): void
    {
        // Subscription non expirée
        $this->subscription->setStartDate(new \DateTimeImmutable('2026-01-01'));
        $this->subscription->setEndDate(new \DateTimeImmutable('2026-12-31'));
        $this->assertFalse($this->subscription->isExpired());
        
        // Subscription expirée
        $this->subscription->setStartDate(new \DateTimeImmutable('2025-01-01'));
        $this->subscription->setEndDate(new \DateTimeImmutable('2025-12-31'));
        $this->assertTrue($this->subscription->isExpired());
    }

    public function testCancel(): void
    {
        $this->subscription->setStatus('active');
        $this->subscription->cancel();
        
        $this->assertEquals('cancelled', $this->subscription->getStatus());
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->subscription->getCancelledAt());
    }

    public function testActivate(): void
    {
        $this->subscription->setStatus('pending');
        $this->subscription->activate();
        
        $this->assertEquals('active', $this->subscription->getStatus());
    }

    public function testExpire(): void
    {
        $this->subscription->setStatus('active');
        $this->subscription->expire();
        
        $this->assertEquals('expired', $this->subscription->getStatus());
    }

    public function testSetUpdatedAt(): void
    {
        $initialUpdatedAt = $this->subscription->getUpdatedAt();
        sleep(1);
        
        $this->subscription->setUpdatedAt(new \DateTimeImmutable());
        
        $this->assertNotEquals($initialUpdatedAt, $this->subscription->getUpdatedAt());
    }
}
