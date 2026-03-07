<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Create deliveries table and remove shipping_address from orders
 */
final class Version20260307000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create deliveries table for physical products shipment and remove shipping_address, shipped_at, delivered_at from orders';
    }

    public function up(Schema $schema): void
    {
        // Create deliveries table
        $this->addSql('CREATE TABLE deliveries (
            id INT AUTO_INCREMENT NOT NULL,
            order_id INT NOT NULL,
            shipping_address JSON NOT NULL,
            status VARCHAR(50) NOT NULL,
            tracking_number VARCHAR(255) DEFAULT NULL,
            carrier VARCHAR(255) DEFAULT NULL,
            shipped_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            estimated_delivery_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            notes LONGTEXT DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_38E1C54E8D9F6D38 (order_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Add foreign key constraint
        $this->addSql('ALTER TABLE deliveries ADD CONSTRAINT FK_38E1C54E8D9F6D38 
            FOREIGN KEY (order_id) REFERENCES `orders` (id)');
        
        // Remove shipping_address, shipped_at, delivered_at columns from orders
        $this->addSql('ALTER TABLE `orders` DROP COLUMN shipping_address');
        $this->addSql('ALTER TABLE `orders` DROP COLUMN shipped_at');
        $this->addSql('ALTER TABLE `orders` DROP COLUMN delivered_at');
    }

    public function down(Schema $schema): void
    {
        // Drop deliveries table and foreign key
        $this->addSql('ALTER TABLE deliveries DROP FOREIGN KEY FK_38E1C54E8D9F6D38');
        $this->addSql('DROP TABLE deliveries');
        
        // Re-add shipping_address, shipped_at, delivered_at columns to orders
        $this->addSql('ALTER TABLE `orders` ADD COLUMN shipping_address JSON NOT NULL');
        $this->addSql('ALTER TABLE `orders` ADD COLUMN shipped_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE `orders` ADD COLUMN delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }
}
