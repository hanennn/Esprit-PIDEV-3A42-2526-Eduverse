<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260216001003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, message LONGTEXT NOT NULL, type VARCHAR(50) NOT NULL, is_read TINYINT(1) NOT NULL, date_creation DATETIME NOT NULL, destinataire_id INT NOT NULL, sujet_id INT DEFAULT NULL, message_ref_id INT DEFAULT NULL, INDEX IDX_BF5476CAA4F84F6E (destinataire_id), INDEX IDX_BF5476CA7C4D497E (sujet_id), INDEX IDX_BF5476CA1690DCA6 (message_ref_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA4F84F6E FOREIGN KEY (destinataire_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA7C4D497E FOREIGN KEY (sujet_id) REFERENCES sujet (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA1690DCA6 FOREIGN KEY (message_ref_id) REFERENCES message (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAA4F84F6E');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA7C4D497E');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA1690DCA6');
        $this->addSql('DROP TABLE notification');
    }
}
