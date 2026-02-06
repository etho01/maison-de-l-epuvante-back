<?php

namespace App\Entity;

use App\Repository\SubscriptionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SubscriptionRepository::class)]
#[ORM\Table(name: 'subscriptions')]
class Subscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['subscription:read', 'subscription:list', 'subscription:detail'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'subscriptions')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['subscription:read', 'subscription:detail'])]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'subscriptions')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['subscription:read', 'subscription:list', 'subscription:detail'])]
    private ?SubscriptionPlan $plan = null;

    #[ORM\Column(length: 50)]
    #[Assert\Choice(choices: ['active', 'cancelled', 'expired', 'pending'])]
    #[Groups(['subscription:read', 'subscription:list', 'subscription:detail', 'subscription:write'])]
    private ?string $status = 'pending';

    #[ORM\Column]
    #[Groups(['subscription:read', 'subscription:detail'])]
    private ?\DateTimeImmutable $startDate = null;

    #[ORM\Column]
    #[Groups(['subscription:read', 'subscription:detail'])]
    private ?\DateTimeImmutable $endDate = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['subscription:read', 'subscription:detail'])]
    private ?\DateTimeImmutable $cancelledAt = null;

    #[ORM\Column]
    #[Groups(['subscription:read', 'subscription:detail', 'subscription:write'])]
    private ?bool $autoRenew = true;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $paymentIntentId = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getPlan(): ?SubscriptionPlan
    {
        return $this->plan;
    }

    public function setPlan(?SubscriptionPlan $plan): static
    {
        $this->plan = $plan;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getStartDate(): ?\DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeImmutable $startDate): static
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeImmutable $endDate): static
    {
        $this->endDate = $endDate;
        return $this;
    }

    public function getCancelledAt(): ?\DateTimeImmutable
    {
        return $this->cancelledAt;
    }

    public function setCancelledAt(?\DateTimeImmutable $cancelledAt): static
    {
        $this->cancelledAt = $cancelledAt;
        return $this;
    }

    public function isAutoRenew(): ?bool
    {
        return $this->autoRenew;
    }

    public function setAutoRenew(bool $autoRenew): static
    {
        $this->autoRenew = $autoRenew;
        return $this;
    }

    public function getPaymentIntentId(): ?string
    {
        return $this->paymentIntentId;
    }

    public function setPaymentIntentId(?string $paymentIntentId): static
    {
        $this->paymentIntentId = $paymentIntentId;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && 
               $this->endDate > new \DateTimeImmutable();
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired' || 
               ($this->endDate !== null && $this->endDate <= new \DateTimeImmutable());
    }

    public function cancel(): static
    {
        $this->status = 'cancelled';
        $this->cancelledAt = new \DateTimeImmutable();
        return $this;
    }

    public function activate(): static
    {
        $this->status = 'active';
        return $this;
    }

    public function expire(): static
    {
        $this->status = 'expired';
        return $this;
    }
}
