<?php

namespace App\Entity;

use App\Repository\SubscriptionPlanRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SubscriptionPlanRepository::class)]
#[ORM\Table(name: 'subscription_plans')]
class SubscriptionPlan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['subscription_plan:read', 'subscription_plan:list', 'subscription_plan:detail'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Groups(['subscription_plan:read', 'subscription_plan:list', 'subscription_plan:detail'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['subscription_plan:read', 'subscription_plan:list', 'subscription_plan:detail'])]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\Positive]
    #[Groups(['subscription_plan:read', 'subscription_plan:list', 'subscription_plan:detail'])]
    private ?string $price = null;

    #[ORM\Column(length: 50)]
    #[Assert\Choice(choices: ['monthly', 'quarterly', 'yearly'])]
    #[Groups(['subscription_plan:read', 'subscription_plan:list', 'subscription_plan:detail'])]
    private ?string $billingInterval = 'monthly';

    #[ORM\Column]
    #[Assert\Positive]
    #[Groups(['subscription_plan:read', 'subscription_plan:list', 'subscription_plan:detail'])]
    private ?int $durationInMonths = 1;

    #[ORM\Column(length: 50)]
    #[Assert\Choice(choices: ['paper', 'digital', 'both'])]
    #[Groups(['subscription_plan:read', 'subscription_plan:list', 'subscription_plan:detail'])]
    private ?string $format = 'both';

    #[ORM\Column]
    #[Groups(['subscription_plan:read', 'subscription_plan:list', 'subscription_plan:detail'])]
    private ?bool $active = true;

    #[ORM\OneToMany(mappedBy: 'plan', targetEntity: Subscription::class)]
    private Collection $subscriptions;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->subscriptions = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;
        return $this;
    }

    public function getBillingInterval(): ?string
    {
        return $this->billingInterval;
    }

    public function setBillingInterval(string $billingInterval): static
    {
        $this->billingInterval = $billingInterval;
        return $this;
    }

    public function getDurationInMonths(): ?int
    {
        return $this->durationInMonths;
    }

    public function setDurationInMonths(int $durationInMonths): static
    {
        $this->durationInMonths = $durationInMonths;
        return $this;
    }

    public function getFormat(): ?string
    {
        return $this->format;
    }

    public function setFormat(string $format): static
    {
        $this->format = $format;
        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;
        return $this;
    }

    /**
     * @return Collection<int, Subscription>
     */
    public function getSubscriptions(): Collection
    {
        return $this->subscriptions;
    }

    public function addSubscription(Subscription $subscription): static
    {
        if (!$this->subscriptions->contains($subscription)) {
            $this->subscriptions->add($subscription);
            $subscription->setPlan($this);
        }

        return $this;
    }

    public function removeSubscription(Subscription $subscription): static
    {
        if ($this->subscriptions->removeElement($subscription)) {
            if ($subscription->getPlan() === $this) {
                $subscription->setPlan(null);
            }
        }

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
}
