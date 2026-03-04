<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260301001703 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE analyse_interview (id INT AUTO_INCREMENT NOT NULL, transcription LONGTEXT DEFAULT NULL, score_determine DOUBLE PRECISION DEFAULT NULL, score_anxieux DOUBLE PRECISION DEFAULT NULL, score_confiant DOUBLE PRECISION DEFAULT NULL, score_motive DOUBLE PRECISION DEFAULT NULL, score_hesitant DOUBLE PRECISION DEFAULT NULL, debit_parole INT DEFAULT NULL, taux_hesitations DOUBLE PRECISION DEFAULT NULL, energie_vocale VARCHAR(50) DEFAULT NULL, profil_global LONGTEXT DEFAULT NULL, recommandation LONGTEXT DEFAULT NULL, chemin_audio VARCHAR(255) DEFAULT NULL, date_interview DATETIME NOT NULL, demande_bourse_id INT NOT NULL, UNIQUE INDEX UNIQ_57BF7C9E3B4B2704 (demande_bourse_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE analyse_interview ADD CONSTRAINT FK_57BF7C9E3B4B2704 FOREIGN KEY (demande_bourse_id) REFERENCES demande_bourse (id)');
        $this->addSql('ALTER TABLE demande_bourse DROP FOREIGN KEY FK_9F86FAF34E67DDD1');
        $this->addSql('ALTER TABLE demande_bourse ADD CONSTRAINT FK_9F86FAF34E67DDD1 FOREIGN KEY (bourse_id) REFERENCES bourse (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE historique DROP FOREIGN KEY FK_971591EEF675F31B');
        $this->addSql('ALTER TABLE historique CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('DROP INDEX idx_971591eef675f31b ON historique');
        $this->addSql('CREATE INDEX IDX_EDBFD5EC10DAF24A ON historique (actor_id)');
        $this->addSql('ALTER TABLE historique ADD CONSTRAINT FK_971591EEF675F31B FOREIGN KEY (actor_id) REFERENCES user (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE analyse_interview DROP FOREIGN KEY FK_57BF7C9E3B4B2704');
        $this->addSql('DROP TABLE analyse_interview');
        $this->addSql('ALTER TABLE demande_bourse DROP FOREIGN KEY FK_9F86FAF34E67DDD1');
        $this->addSql('ALTER TABLE demande_bourse ADD CONSTRAINT FK_9F86FAF34E67DDD1 FOREIGN KEY (bourse_id) REFERENCES bourse (id)');
        $this->addSql('ALTER TABLE historique DROP FOREIGN KEY FK_EDBFD5EC10DAF24A');
        $this->addSql('ALTER TABLE historique CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('DROP INDEX idx_edbfd5ec10daf24a ON historique');
        $this->addSql('CREATE INDEX IDX_971591EEF675F31B ON historique (actor_id)');
        $this->addSql('ALTER TABLE historique ADD CONSTRAINT FK_EDBFD5EC10DAF24A FOREIGN KEY (actor_id) REFERENCES `user` (id) ON DELETE SET NULL');
    }
}
