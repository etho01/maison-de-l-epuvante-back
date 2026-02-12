<?php

namespace App\Ecommerce\Entity;

use App\Ecommerce\Repository\ProductRepository;
use App\Entity\DigitalContent;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\Table(name: 'products')]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['product:read', 'product:list', 'product:detail', 'order:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Groups(['product:read', 'product:list', 'product:detail', 'product:write', 'order:read'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['product:read', 'product:detail', 'product:write'])]
    private ?string $description = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank]
    #[Groups(['product:read', 'product:list', 'product:detail', 'product:write'])]
    private ?string $slug = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank]
    #[Assert\Positive]
    #[Groups(['product:read', 'product:list', 'product:detail', 'product:write', 'order:read'])]
    private ?string $price = null;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    #[Groups(['product:read', 'product:detail', 'product:write'])]
    private ?int $stock = 0;

    #[ORM\Column(length: 50)]
    #[Assert\Choice(choices: ['physical', 'digital', 'subscription'])]
    #[Groups(['product:read', 'product:detail', 'product:write'])]
    private ?string $type = 'physical';

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['product:read', 'product:detail', 'product:write'])]
    private ?string $sku = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups(['product:read', 'product:list', 'product:detail', 'product:write'])]
    private ?array $images = [];

    #[ORM\Column]
    #[Groups(['product:read', 'product:detail', 'product:write'])]
    private ?bool $active = true;

    #[ORM\Column]
    #[Groups(['product:read', 'product:detail', 'product:write'])]
    private ?bool $exclusiveOnline = false;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['product:read', 'product:detail', 'product:write'])]
    private ?Category $category = null;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: OrderItem::class)]
    private Collection $orderItems;

    #[ORM\OneToOne(mappedBy: 'product', cascade: ['persist', 'remove'])]    private ?DigitalContent $digitalContent = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Groups(['product:read', 'product:detail', 'product:write'])]
    private ?string $weight = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $metadata = [];

    public function __construct()
    {
        $this->orderItems = new ArrayCollection();
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;
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

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): static
    {
        $this->stock = $stock;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getSku(): ?string
    {
        return $this->sku;
    }

    public function setSku(?string $sku): static
    {
        $this->sku = $sku;
        return $this;
    }

    public function getImages(): ?array
    {
        return $this->images;
    }

    public function setImages(?array $images): static
    {
        $this->images = $images;
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

    public function isExclusiveOnline(): ?bool
    {
        return $this->exclusiveOnline;
    }

    public function setExclusiveOnline(bool $exclusiveOnline): static
    {
        $this->exclusiveOnline = $exclusiveOnline;
        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return Collection<int, OrderItem>
     */
    public function getOrderItems(): Collection
    {
        return $this->orderItems;
    }

    public function addOrderItem(OrderItem $orderItem): static
    {
        if (!$this->orderItems->contains($orderItem)) {
            $this->orderItems->add($orderItem);
            $orderItem->setProduct($this);
        }

        return $this;
    }

    public function removeOrderItem(OrderItem $orderItem): static
    {
        if ($this->orderItems->removeElement($orderItem)) {
            if ($orderItem->getProduct() === $this) {
                $orderItem->setProduct(null);
            }
        }

        return $this;
    }

    public function getDigitalContent(): ?DigitalContent
    {
        return $this->digitalContent;
    }

    public function setDigitalContent(?DigitalContent $digitalContent): static
    {
        if ($digitalContent === null && $this->digitalContent !== null) {
            $this->digitalContent->setProduct(null);
        }

        if ($digitalContent !== null && $digitalContent->getProduct() !== $this) {
            $digitalContent->setProduct($this);
        }

        $this->digitalContent = $digitalContent;

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

    public function getWeight(): ?string
    {
        return $this->weight;
    }

    public function setWeight(?string $weight): static
    {
        $this->weight = $weight;
        return $this;
    }

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function setMetadata(?array $metadata): static
    {
        $this->metadata = $metadata;
        return $this;
    }

    public function isPhysical(): bool
    {
        return $this->type === 'physical';
    }

    public function isDigital(): bool
    {
        return $this->type === 'digital';
    }

    public function isSubscription(): bool
    {
        return $this->type === 'subscription';
    }

    public function isInStock(): bool
    {
        return $this->stock > 0;
    }

    public function decreaseStock(int $quantity): static
    {
        $this->stock -= $quantity;
        if ($this->stock < 0) {
            $this->stock = 0;
        }
        return $this;
    }

    public function increaseStock(int $quantity): static
    {
        $this->stock += $quantity;
        return $this;
    }
}
