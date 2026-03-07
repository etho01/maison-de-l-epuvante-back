<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add delivery_id to order_items to link items with their delivery
 */
final class Version20260307000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add delivery_id column to order_items table to link physical items with their delivery';
    }

    public function up(Schema $schema): void
    {
        // Add delivery_id column to order_items
        $this->addSql('ALTER TABLE order_items ADD COLUMN delivery_id INT DEFAULT NULL');
        
        // Add index for performance
        $this->addSql('CREATE INDEX IDX_62809DB012136921 ON order_items (delivery_id)');
        
        // Add foreign key constraint
        $this->addSql('ALTER TABLE order_items ADD CONSTRAINT FK_62809DB012136921 
            FOREIGN KEY (delivery_id) REFERENCES deliveries (id)');
    }

    public function down(Schema $schema): void
    {
        // Drop foreign key constraint
        $this->addSql('ALTER TABLE order_items DROP FOREIGN KEY FK_62809DB012136921');
        
        // Drop index
        $this->addSql('DROP INDEX IDX_62809DB012136921 ON order_items');
        
        // Drop column
        $this->addSql('ALTER TABLE order_items DROP COLUMN delivery_id');
    }
}
