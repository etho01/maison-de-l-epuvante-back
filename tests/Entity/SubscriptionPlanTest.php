<?php

namespace App\Tests\Entity;

use App\Entity\Subscription;
use App\Entity\SubscriptionPlan;
use PHPUnit\Framework\TestCase;

class SubscriptionPlanTest extends TestCase
{
    private SubscriptionPlan $plan;

    protected function setUp(): void
    {
        $this->plan = new SubscriptionPlan();
    }

    public function testNewPlanHasCorrectDefaults(): void
    {
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->plan->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->plan->getUpdatedAt());
        $this->assertTrue($this->plan->isActive());
        $this->assertEquals('monthly', $this->plan->getBillingInterval());
        $this->assertEquals(1, $this->plan->getDurationInMonths());
        $this->assertEquals('both', $this->plan->getFormat());
        $this->assertCount(0, $this->plan->getSubscriptions());
    }

    public function testSetAndGetName(): void
    {
        $name = 'Premium Plan';
        $this->plan->setName($name);
        
        $this->assertEquals($name, $this->plan->getName());
    }

    public function testSetAndGetDescription(): void
    {
        $description = 'Un plan premium avec tous les avantages';
        $this->plan->setDescription($description);
        
        $this->assertEquals($description, $this->plan->getDescription());
    }

    public function testSetAndGetPrice(): void
    {
        $price = '29.99';
        $this->plan->setPrice($price);
        
        $this->assertEquals($price, $this->plan->getPrice());
    }

    public function testSetAndGetBillingInterval(): void
    {
        $intervals = ['monthly', 'quarterly', 'yearly'];
        
        foreach ($intervals as $interval) {
            $this->plan->setBillingInterval($interval);
            $this->assertEquals($interval, $this->plan->getBillingInterval());
        }
    }

    public function testSetAndGetDurationInMonths(): void
    {
        $duration = 12;
        $this->plan->setDurationInMonths($duration);
        
        $this->assertEquals($duration, $this->plan->getDurationInMonths());
    }

    public function testSetAndGetFormat(): void
    {
        $formats = ['paper', 'digital', 'both'];
        
        foreach ($formats as $format) {
            $this->plan->setFormat($format);
            $this->assertEquals($format, $this->plan->getFormat());
        }
    }

    public function testSetAndGetActive(): void
    {
        $this->plan->setActive(false);
        $this->assertFalse($this->plan->isActive());
        
        $this->plan->setActive(true);
        $this->assertTrue($this->plan->isActive());
    }

    public function testAddAndRemoveSubscription(): void
    {
        $subscription = $this->createMock(Subscription::class);
        $subscription->expects($this->once())
            ->method('setPlan')
            ->with($this->plan);
        
        $this->plan->addSubscription($subscription);
        $this->assertCount(1, $this->plan->getSubscriptions());
        
        $this->plan->removeSubscription($subscription);
        $this->assertCount(0, $this->plan->getSubscriptions());
    }

    public function testSetUpdatedAt(): void
    {
        $initialUpdatedAt = $this->plan->getUpdatedAt();
        sleep(1);
        
        $this->plan->setUpdatedAt(new \DateTimeImmutable());
        
        $this->assertNotEquals($initialUpdatedAt, $this->plan->getUpdatedAt());
    }

    public function testMonthlyPlanDuration(): void
    {
        $this->plan->setBillingInterval('monthly');
        $this->plan->setDurationInMonths(1);
        
        $this->assertEquals('monthly', $this->plan->getBillingInterval());
        $this->assertEquals(1, $this->plan->getDurationInMonths());
    }

    public function testQuarterlyPlanDuration(): void
    {
        $this->plan->setBillingInterval('quarterly');
        $this->plan->setDurationInMonths(3);
        
        $this->assertEquals('quarterly', $this->plan->getBillingInterval());
        $this->assertEquals(3, $this->plan->getDurationInMonths());
    }

    public function testYearlyPlanDuration(): void
    {
        $this->plan->setBillingInterval('yearly');
        $this->plan->setDurationInMonths(12);
        
        $this->assertEquals('yearly', $this->plan->getBillingInterval());
        $this->assertEquals(12, $this->plan->getDurationInMonths());
    }
}
