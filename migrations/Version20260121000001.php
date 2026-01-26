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
            id SERIAL PRIMARY KEY,
            user_id INT NOT NULL,
            token VARCHAR(100) NOT NULL UNIQUE,
            expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            CONSTRAINT fk_reset_password_request_user FOREIGN KEY (user_id) 
                REFERENCES "user" (id) ON DELETE CASCADE
        )');
        
        $this->addSql('CREATE INDEX idx_reset_password_request_user ON reset_password_request (user_id)');
        $this->addSql('CREATE INDEX idx_reset_password_request_token ON reset_password_request (token)');
        
        $this->addSql('COMMENT ON COLUMN reset_password_request.expires_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN reset_password_request.created_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS reset_password_request');
    }
}
