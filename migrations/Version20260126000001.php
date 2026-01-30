<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * E-Commerce tables migration
 */
final class Version20260126000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create e-commerce tables: categories, products, carts, orders, subscriptions, digital_contents';
    }

    public function up(Schema $schema): void
    {
        // Categories table
        $this->addSql('CREATE TABLE categories (
            id INT AUTO_INCREMENT NOT NULL,
            parent_id INT DEFAULT NULL,
            name VARCHAR(255) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            slug VARCHAR(255) NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_3AF34668989D9B62 (slug),
            INDEX IDX_3AF34668727ACA70 (parent_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE categories ADD CONSTRAINT FK_3AF34668727ACA70 
            FOREIGN KEY (parent_id) REFERENCES categories (id) ON DELETE CASCADE');

        // Products table
        $this->addSql('CREATE TABLE products (
            id INT AUTO_INCREMENT NOT NULL,
            category_id INT DEFAULT NULL,
            name VARCHAR(255) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            slug VARCHAR(255) NOT NULL,
            price NUMERIC(10, 2) NOT NULL,
            stock INT NOT NULL,
            type VARCHAR(50) NOT NULL,
            sku VARCHAR(255) DEFAULT NULL,
            images JSON DEFAULT NULL,
            active TINYINT(1) NOT NULL,
            exclusive_online TINYINT(1) NOT NULL,
            weight NUMERIC(10, 2) DEFAULT NULL,
            metadata JSON DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_B3BA5A5A989D9B62 (slug),
            INDEX IDX_B3BA5A5A12469DE2 (category_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE products ADD CONSTRAINT FK_B3BA5A5A12469DE2 
            FOREIGN KEY (category_id) REFERENCES categories (id)');

        // Carts table
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

        // Cart items table
        $this->addSql('CREATE TABLE cart_items (
            id INT AUTO_INCREMENT NOT NULL,
            cart_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_BEF484451AD5CDBF (cart_id),
            INDEX IDX_BEF484454584665A (product_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE cart_items ADD CONSTRAINT FK_BEF484451AD5CDBF 
            FOREIGN KEY (cart_id) REFERENCES carts (id)');
        $this->addSql('ALTER TABLE cart_items ADD CONSTRAINT FK_BEF484454584665A 
            FOREIGN KEY (product_id) REFERENCES products (id)');

        // Orders table
        $this->addSql('CREATE TABLE `orders` (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            order_number VARCHAR(255) NOT NULL,
            status VARCHAR(50) NOT NULL,
            total_amount NUMERIC(10, 2) NOT NULL,
            shipping_cost NUMERIC(10, 2) NOT NULL,
            tax_amount NUMERIC(10, 2) NOT NULL,
            shipping_address JSON NOT NULL,
            billing_address JSON NOT NULL,
            payment_method VARCHAR(50) DEFAULT NULL,
            payment_intent_id VARCHAR(255) DEFAULT NULL,
            customer_notes LONGTEXT DEFAULT NULL,
            admin_notes LONGTEXT DEFAULT NULL,
            paid_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            shipped_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_E52FFDEE551F0F81 (order_number),
            INDEX IDX_E52FFDEEA76ED395 (user_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE `orders` ADD CONSTRAINT FK_E52FFDEEA76ED395 
            FOREIGN KEY (user_id) REFERENCES `user` (id)');

        // Order items table
        $this->addSql('CREATE TABLE order_items (
            id INT AUTO_INCREMENT NOT NULL,
            order_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL,
            unit_price NUMERIC(10, 2) NOT NULL,
            total_price NUMERIC(10, 2) NOT NULL,
            product_name VARCHAR(255) NOT NULL,
            product_sku VARCHAR(255) DEFAULT NULL,
            INDEX IDX_62809DB08D9F6D38 (order_id),
            INDEX IDX_62809DB04584665A (product_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE order_items ADD CONSTRAINT FK_62809DB08D9F6D38 
            FOREIGN KEY (order_id) REFERENCES `orders` (id)');
        $this->addSql('ALTER TABLE order_items ADD CONSTRAINT FK_62809DB04584665A 
            FOREIGN KEY (product_id) REFERENCES products (id)');

        // Digital contents table
        $this->addSql('CREATE TABLE digital_contents (
            id INT AUTO_INCREMENT NOT NULL,
            product_id INT NOT NULL,
            content_type VARCHAR(50) NOT NULL,
            file_path VARCHAR(500) NOT NULL,
            file_name VARCHAR(255) DEFAULT NULL,
            file_size BIGINT DEFAULT NULL,
            mime_type VARCHAR(100) DEFAULT NULL,
            issue_number INT DEFAULT NULL,
            page_count INT DEFAULT NULL,
            metadata JSON DEFAULT NULL,
            requires_subscription TINYINT(1) NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_8B9A6F364584665A (product_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE digital_contents ADD CONSTRAINT FK_8B9A6F364584665A 
            FOREIGN KEY (product_id) REFERENCES products (id)');

        // Subscription plans table
        $this->addSql('CREATE TABLE subscription_plans (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(255) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            price NUMERIC(10, 2) NOT NULL,
            billing_interval VARCHAR(50) NOT NULL,
            duration_in_months INT NOT NULL,
            format VARCHAR(50) NOT NULL,
            active TINYINT(1) NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Subscriptions table
        $this->addSql('CREATE TABLE subscriptions (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            plan_id INT NOT NULL,
            status VARCHAR(50) NOT NULL,
            start_date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            end_date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            cancelled_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            auto_renew TINYINT(1) NOT NULL,
            payment_intent_id VARCHAR(255) DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_4778A01A76ED395 (user_id),
            INDEX IDX_4778A01E899029B (plan_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE subscriptions ADD CONSTRAINT FK_4778A01A76ED395 
            FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE subscriptions ADD CONSTRAINT FK_4778A01E899029B 
            FOREIGN KEY (plan_id) REFERENCES subscription_plans (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE subscriptions DROP FOREIGN KEY FK_4778A01A76ED395');
        $this->addSql('ALTER TABLE subscriptions DROP FOREIGN KEY FK_4778A01E899029B');
        $this->addSql('ALTER TABLE digital_contents DROP FOREIGN KEY FK_8B9A6F364584665A');
        $this->addSql('ALTER TABLE order_items DROP FOREIGN KEY FK_62809DB08D9F6D38');
        $this->addSql('ALTER TABLE order_items DROP FOREIGN KEY FK_62809DB04584665A');
        $this->addSql('ALTER TABLE `orders` DROP FOREIGN KEY FK_E52FFDEEA76ED395');
        $this->addSql('ALTER TABLE cart_items DROP FOREIGN KEY FK_BEF484451AD5CDBF');
        $this->addSql('ALTER TABLE cart_items DROP FOREIGN KEY FK_BEF484454584665A');
        $this->addSql('ALTER TABLE carts DROP FOREIGN KEY FK_4E004AACA76ED395');
        $this->addSql('ALTER TABLE products DROP FOREIGN KEY FK_B3BA5A5A12469DE2');
        $this->addSql('ALTER TABLE categories DROP FOREIGN KEY FK_3AF34668727ACA70');

        $this->addSql('DROP TABLE subscriptions');
        $this->addSql('DROP TABLE subscription_plans');
        $this->addSql('DROP TABLE digital_contents');
        $this->addSql('DROP TABLE order_items');
        $this->addSql('DROP TABLE `orders`');
        $this->addSql('DROP TABLE cart_items');
        $this->addSql('DROP TABLE carts');
        $this->addSql('DROP TABLE products');
        $this->addSql('DROP TABLE categories');
    }
}
