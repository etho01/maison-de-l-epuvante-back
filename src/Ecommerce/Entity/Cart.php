<?php

namespace App\Ecommerce\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use App\Ecommerce\Repository\CartRepository;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CartRepository::class)]
#[ORM\Table(name: 'carts')]
#[ApiResource(
    shortName: 'Cart',
    operations: [
        new Get(
            uriTemplate: '/cart/me',
            security: "is_granted('ROLE_USER')",
            normalizationContext: ['groups' => ['cart:read', 'cart:detail']],
            name: 'api_cart_me',
        ),
        new Post(
            uriTemplate: '/cart/items',
            security: "is_granted('ROLE_USER')",
            denormalizationContext: ['groups' => ['cart:write']],
            name: 'api_cart_add_item',
        ),
        new Patch(
            uriTemplate: '/cart/items/{itemId}',
            security: "is_granted('ROLE_USER')",
            denormalizationContext: ['groups' => ['cart:write']],
            name: 'api_cart_update_item',
        ),
        new Delete(
            uriTemplate: '/cart/items/{itemId}',
            security: "is_granted('ROLE_USER')",
            name: 'api_cart_remove_item',
        ),
        new Delete(
            uriTemplate: '/cart/clear',
            security: "is_granted('ROLE_USER')",
            name: 'api_cart_clear',
        ),
    ],
    normalizationContext: ['groups' => ['cart:read']],
    denormalizationContext: ['groups' => ['cart:write']],
)]
class Cart
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['cart:read', 'cart:detail'])]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'cart')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\OneToMany(mappedBy: 'cart', targetEntity: CartItem::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['cart:read', 'cart:detail'])]
    private Collection $items;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return Collection<int, CartItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(CartItem $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setCart($this);
        }

        return $this;
    }

    public function removeItem(CartItem $item): static
    {
        if ($this->items->removeElement($item)) {
            if ($item->getCart() === $this) {
                $item->setCart(null);
            }
        }

        return $this;
    }

    public function getTotal(): float
    {
        $total = 0;
        foreach ($this->items as $item) {
            $total += (float)$item->getProduct()->getPrice() * $item->getQuantity();
        }
        return $total;
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
