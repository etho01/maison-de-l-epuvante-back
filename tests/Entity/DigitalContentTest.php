<?php

namespace App\Tests\Entity;

use App\Entity\DigitalContent;
use App\Ecommerce\Entity\Product;
use PHPUnit\Framework\TestCase;

class DigitalContentTest extends TestCase
{
    private DigitalContent $content;

    protected function setUp(): void
    {
        $this->content = new DigitalContent();
    }

    public function testNewContentHasCorrectDefaults(): void
    {
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->content->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->content->getUpdatedAt());
        $this->assertEquals('fanzine', $this->content->getContentType());
        $this->assertFalse($this->content->isRequiresSubscription());
        $this->assertEquals([], $this->content->getMetadata());
    }

    public function testSetAndGetProduct(): void
    {
        $product = $this->createMock(Product::class);
        $this->content->setProduct($product);
        
        $this->assertEquals($product, $this->content->getProduct());
    }

    public function testSetAndGetContentType(): void
    {
        $types = ['fanzine', 'ebook', 'video', 'audio', 'other'];
        
        foreach ($types as $type) {
            $this->content->setContentType($type);
            $this->assertEquals($type, $this->content->getContentType());
        }
    }

    public function testSetAndGetFilePath(): void
    {
        $filePath = '/uploads/content/file.pdf';
        $this->content->setFilePath($filePath);
        
        $this->assertEquals($filePath, $this->content->getFilePath());
    }

    public function testSetAndGetFileName(): void
    {
        $fileName = 'my-document.pdf';
        $this->content->setFileName($fileName);
        
        $this->assertEquals($fileName, $this->content->getFileName());
    }

    public function testSetAndGetFileSize(): void
    {
        $fileSize = '1024000'; // 1MB
        $this->content->setFileSize($fileSize);
        
        $this->assertEquals($fileSize, $this->content->getFileSize());
    }

    public function testSetAndGetMimeType(): void
    {
        $mimeType = 'application/pdf';
        $this->content->setMimeType($mimeType);
        
        $this->assertEquals($mimeType, $this->content->getMimeType());
    }

    public function testSetAndGetIssueNumber(): void
    {
        $issueNumber = 42;
        $this->content->setIssueNumber($issueNumber);
        
        $this->assertEquals($issueNumber, $this->content->getIssueNumber());
    }

    public function testSetAndGetPageCount(): void
    {
        $pageCount = 120;
        $this->content->setPageCount($pageCount);
        
        $this->assertEquals($pageCount, $this->content->getPageCount());
    }

    public function testSetAndGetMetadata(): void
    {
        $metadata = ['author' => 'John Doe', 'year' => 2026];
        $this->content->setMetadata($metadata);
        
        $this->assertEquals($metadata, $this->content->getMetadata());
    }

    public function testSetAndGetRequiresSubscription(): void
    {
        $this->content->setRequiresSubscription(true);
        $this->assertTrue($this->content->isRequiresSubscription());
        
        $this->content->setRequiresSubscription(false);
        $this->assertFalse($this->content->isRequiresSubscription());
    }

    public function testIsFanzine(): void
    {
        $this->content->setContentType('fanzine');
        $this->assertTrue($this->content->isFanzine());
        
        $this->content->setContentType('ebook');
        $this->assertFalse($this->content->isFanzine());
    }

    public function testIsEbook(): void
    {
        $this->content->setContentType('ebook');
        $this->assertTrue($this->content->isEbook());
        
        $this->content->setContentType('fanzine');
        $this->assertFalse($this->content->isEbook());
    }

    public function testIsVideo(): void
    {
        $this->content->setContentType('video');
        $this->assertTrue($this->content->isVideo());
        
        $this->content->setContentType('audio');
        $this->assertFalse($this->content->isVideo());
    }

    public function testIsAudio(): void
    {
        $this->content->setContentType('audio');
        $this->assertTrue($this->content->isAudio());
        
        $this->content->setContentType('video');
        $this->assertFalse($this->content->isAudio());
    }

    public function testSetUpdatedAt(): void
    {
        $initialUpdatedAt = $this->content->getUpdatedAt();
        sleep(1);
        
        $this->content->setUpdatedAt(new \DateTimeImmutable());
        
        $this->assertNotEquals($initialUpdatedAt, $this->content->getUpdatedAt());
    }
}
