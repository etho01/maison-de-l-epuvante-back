<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Drop cart and cart_items tables
 */
final class Version20260212000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove cart and cart_items tables from the database';
    }

    public function up(Schema $schema): void
    {
        // Drop foreign keys first
        $this->addSql('ALTER TABLE cart_items DROP FOREIGN KEY FK_BEF484451AD5CDBF');
        $this->addSql('ALTER TABLE cart_items DROP FOREIGN KEY FK_BEF484454584665A');
        $this->addSql('ALTER TABLE carts DROP FOREIGN KEY FK_4E004AACA76ED395');
        
        // Drop tables
        $this->addSql('DROP TABLE cart_items');
        $this->addSql('DROP TABLE carts');
    }

    public function down(Schema $schema): void
    {
        // Recreate carts table
        $this->addSql('CREATE TABLE carts (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_4E004AACA76ED395 (user_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        $this->addSql('ALTER TABLE carts ADD CONSTRAINT FK_4E004AACA76ED395 
            FOREIGN KEY (user_id) REFERENCES `user` (id)');
        
        // Recreate cart_items table
        $this->addSql('CREATE TABLE cart_items (
            id INT AUTO_INCREMENT NOT NULL,
            cart_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL,
            INDEX IDX_BEF484451AD5CDBF (cart_id),
            INDEX IDX_BEF484454584665A (product_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        $this->addSql('ALTER TABLE cart_items ADD CONSTRAINT FK_BEF484451AD5CDBF 
            FOREIGN KEY (cart_id) REFERENCES carts (id)');
        $this->addSql('ALTER TABLE cart_items ADD CONSTRAINT FK_BEF484454584665A 
            FOREIGN KEY (product_id) REFERENCES products (id)');
    }
}
