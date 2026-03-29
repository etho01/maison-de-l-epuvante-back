<?php

namespace App\Ecommerce\Entity;

use App\Ecommerce\Enum\DeliveryStatus;
use App\Ecommerce\Repository\DeliveryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DeliveryRepository::class)]
#[ORM\Table(name: 'deliveries')]
class Delivery
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['delivery:read', 'delivery:list', 'order:read', 'order:detail'])]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'delivery', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['delivery:read', 'delivery:detail'])]
    private ?Order $order = null;

    #[ORM\OneToMany(mappedBy: 'delivery', targetEntity: OrderItem::class)]
    #[Groups(['delivery:orderItem'])]
    private Collection $items;

    #[ORM\Column(type: Types::JSON)]
    #[Assert\NotBlank]
    #[Groups(['delivery:read', 'delivery:detail', 'delivery:write', 'order:read', 'order:detail'])]
    private array $shippingAddress = [];

    #[ORM\Column(length: 50)]
    #[Assert\Choice(callback: [DeliveryStatus::class, 'values'])]
    #[Groups(['delivery:read', 'delivery:list', 'delivery:detail', 'delivery:write', 'order:read', 'order:detail'])]
    private ?string $status = DeliveryStatus::PENDING->value;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['delivery:read', 'delivery:list', 'delivery:detail', 'delivery:write'])]
    private ?string $trackingNumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['delivery:read', 'delivery:list', 'delivery:detail', 'delivery:write'])]
    private ?string $carrier = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['delivery:read', 'delivery:list', 'delivery:detail', 'order:detail'])]
    private ?\DateTimeImmutable $shippedAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['delivery:read', 'delivery:list', 'delivery:detail', 'order:detail'])]
    private ?\DateTimeImmutable $deliveredAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['delivery:read', 'delivery:detail', 'order:detail'])]
    private ?\DateTimeImmutable $estimatedDeliveryDate = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['delivery:read', 'delivery:detail', 'delivery:write'])]
    private ?string $notes = null;

    #[ORM\Column]
    #[Groups(['delivery:read', 'delivery:list', 'delivery:detail'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['delivery:read', 'delivery:list', 'delivery:detail'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(Order $order): static
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @return Collection<int, OrderItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(OrderItem $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setDelivery($this);
        }

        return $this;
    }

    public function removeItem(OrderItem $item): static
    {
        if ($this->items->removeElement($item)) {
            if ($item->getDelivery() === $this) {
                $item->setDelivery(null);
            }
        }

        return $this;
    }

    public function getShippingAddress(): array
    {
        return $this->shippingAddress;
    }

    public function setShippingAddress(array $shippingAddress): static
    {
        $this->shippingAddress = $shippingAddress;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getTrackingNumber(): ?string
    {
        return $this->trackingNumber;
    }

    public function setTrackingNumber(?string $trackingNumber): static
    {
        $this->trackingNumber = $trackingNumber;
        return $this;
    }

    public function getCarrier(): ?string
    {
        return $this->carrier;
    }

    public function setCarrier(?string $carrier): static
    {
        $this->carrier = $carrier;
        return $this;
    }

    public function getShippedAt(): ?\DateTimeImmutable
    {
        return $this->shippedAt;
    }

    public function setShippedAt(?\DateTimeImmutable $shippedAt): static
    {
        $this->shippedAt = $shippedAt;
        return $this;
    }

    public function getDeliveredAt(): ?\DateTimeImmutable
    {
        return $this->deliveredAt;
    }

    public function setDeliveredAt(?\DateTimeImmutable $deliveredAt): static
    {
        $this->deliveredAt = $deliveredAt;
        return $this;
    }

    public function getEstimatedDeliveryDate(): ?\DateTimeImmutable
    {
        return $this->estimatedDeliveryDate;
    }

    public function setEstimatedDeliveryDate(?\DateTimeImmutable $estimatedDeliveryDate): static
    {
        $this->estimatedDeliveryDate = $estimatedDeliveryDate;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;
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

    public function markAsShipped(?string $trackingNumber = null, ?string $carrier = null): static
    {
        $this->status = DeliveryStatus::SHIPPED->value;
        $this->shippedAt = new \DateTimeImmutable();
        
        if ($trackingNumber) {
            $this->trackingNumber = $trackingNumber;
        }
        
        if ($carrier) {
            $this->carrier = $carrier;
        }
        
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function markAsDelivered(): static
    {
        $this->status = DeliveryStatus::DELIVERED->value;
        $this->deliveredAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function isPending(): bool
    {
        return $this->status === DeliveryStatus::PENDING->value;
    }

    public function isShipped(): bool
    {
        return $this->status === DeliveryStatus::SHIPPED->value;
    }

    public function isDelivered(): bool
    {
        return $this->status === DeliveryStatus::DELIVERED->value;
    }
}
