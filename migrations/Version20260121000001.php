<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour créer la table reset_password_request
 */
final class Version20260121000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Créer la table reset_password_request pour la réinitialisation des mots de passe';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE reset_password_request (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            token VARCHAR(100) NOT NULL UNIQUE,
            expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX idx_reset_password_request_user (user_id),
            INDEX idx_reset_password_request_token (token),
            CONSTRAINT fk_reset_password_request_user FOREIGN KEY (user_id) 
                REFERENCES `user` (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS reset_password_request');
    }
}
