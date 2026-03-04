<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260224091500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create historique table for forum sujet/message CRUD events';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE historique (id INT AUTO_INCREMENT NOT NULL, actor_id INT DEFAULT NULL, entity_type VARCHAR(50) NOT NULL, action VARCHAR(20) NOT NULL, entity_id INT DEFAULT NULL, description LONGTEXT NOT NULL, actor_identifier VARCHAR(180) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_971591EEF675F31B (actor_id), INDEX idx_historique_created_at (created_at), INDEX idx_historique_entity_type (entity_type), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql('ALTER TABLE historique ADD CONSTRAINT FK_971591EEF675F31B FOREIGN KEY (actor_id) REFERENCES `user` (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE historique DROP FOREIGN KEY FK_971591EEF675F31B');
        $this->addSql('DROP TABLE historique');
    }
}
