<?php

namespace App\Entity;

use App\Ecommerce\Entity\Product;
use App\Repository\DigitalContentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: DigitalContentRepository::class)]
#[ORM\Table(name: 'digital_contents')]
class DigitalContent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['digital_content:read', 'digital_content:list', 'digital_content:detail'])]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'digitalContent')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['digital_content:read', 'digital_content:detail'])]
    private ?Product $product = null;

    #[ORM\Column(length: 50)]
    #[Assert\Choice(choices: ['fanzine', 'ebook', 'video', 'audio', 'other'])]
    #[Groups(['digital_content:read', 'digital_content:list', 'digital_content:detail', 'digital_content:write'])]
    private ?string $contentType = 'fanzine';

    #[ORM\Column(length: 500)]
    #[Groups(['digital_content:read', 'digital_content:detail'])]
    private ?string $filePath = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['digital_content:read', 'digital_content:list', 'digital_content:detail', 'digital_content:write'])]
    private ?string $fileName = null;

    #[ORM\Column(type: Types::BIGINT, nullable: true)]
    #[Groups(['digital_content:read', 'digital_content:detail'])]
    private ?string $fileSize = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['digital_content:read', 'digital_content:detail'])]
    private ?string $mimeType = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['digital_content:read', 'digital_content:list', 'digital_content:detail', 'digital_content:write'])]
    private ?int $issueNumber = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['digital_content:read', 'digital_content:list', 'digital_content:detail', 'digital_content:write'])]
    private ?int $pageCount = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups(['digital_content:read', 'digital_content:detail', 'digital_content:write'])]
    private ?array $metadata = [];

    #[ORM\Column]
    #[Groups(['digital_content:read', 'digital_content:detail', 'digital_content:write'])]
    private ?bool $requiresSubscription = false;

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

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;
        return $this;
    }

    public function getContentType(): ?string
    {
        return $this->contentType;
    }

    public function setContentType(string $contentType): static
    {
        $this->contentType = $contentType;
        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(string $filePath): static
    {
        $this->filePath = $filePath;
        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(?string $fileName): static
    {
        $this->fileName = $fileName;
        return $this;
    }

    public function getFileSize(): ?string
    {
        return $this->fileSize;
    }

    public function setFileSize(?string $fileSize): static
    {
        $this->fileSize = $fileSize;
        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(?string $mimeType): static
    {
        $this->mimeType = $mimeType;
        return $this;
    }

    public function getIssueNumber(): ?int
    {
        return $this->issueNumber;
    }

    public function setIssueNumber(?int $issueNumber): static
    {
        $this->issueNumber = $issueNumber;
        return $this;
    }

    public function getPageCount(): ?int
    {
        return $this->pageCount;
    }

    public function setPageCount(?int $pageCount): static
    {
        $this->pageCount = $pageCount;
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

    public function requiresSubscription(): ?bool
    {
        return $this->requiresSubscription;
    }

    public function setRequiresSubscription(bool $requiresSubscription): static
    {
        $this->requiresSubscription = $requiresSubscription;
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
