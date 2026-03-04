<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260303231037 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE analyse_interview (id INT AUTO_INCREMENT NOT NULL, transcription LONGTEXT DEFAULT NULL, score_determine DOUBLE PRECISION DEFAULT NULL, score_anxieux DOUBLE PRECISION DEFAULT NULL, score_confiant DOUBLE PRECISION DEFAULT NULL, score_motive DOUBLE PRECISION DEFAULT NULL, score_hesitant DOUBLE PRECISION DEFAULT NULL, debit_parole INT DEFAULT NULL, taux_hesitations DOUBLE PRECISION DEFAULT NULL, energie_vocale VARCHAR(50) DEFAULT NULL, profil_global LONGTEXT DEFAULT NULL, recommandation LONGTEXT DEFAULT NULL, chemin_audio VARCHAR(255) DEFAULT NULL, date_interview DATETIME NOT NULL, demande_bourse_id INT NOT NULL, UNIQUE INDEX UNIQ_57BF7C9E3B4B2704 (demande_bourse_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE bourse (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, image VARCHAR(255) DEFAULT NULL, date_attribution DATETIME NOT NULL, date_fin DATETIME NOT NULL, montant DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE certification (id INT AUTO_INCREMENT NOT NULL, score_obtenu DOUBLE PRECISION NOT NULL, statut VARCHAR(255) NOT NULL, badge VARCHAR(255) NOT NULL, date_attribution DATETIME NOT NULL, user_id INT NOT NULL, quiz_id INT NOT NULL, INDEX IDX_6C3C6D75A76ED395 (user_id), INDEX IDX_6C3C6D75853CD175 (quiz_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE certification_finale (id INT AUTO_INCREMENT NOT NULL, date_emission DATETIME NOT NULL, badge VARCHAR(50) NOT NULL, user_id INT NOT NULL, quiz_id INT NOT NULL, tentative_id INT NOT NULL, INDEX IDX_7B19D134A76ED395 (user_id), INDEX IDX_7B19D134853CD175 (quiz_id), UNIQUE INDEX UNIQ_7B19D134D78CE477 (tentative_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE chapitres (id INT AUTO_INCREMENT NOT NULL, titre_chap VARCHAR(255) NOT NULL, desc_chap LONGTEXT NOT NULL, ordre_chap INT NOT NULL, duree_chap VARCHAR(255) NOT NULL, statut_chap VARCHAR(20) NOT NULL, resume_chap LONGTEXT DEFAULT NULL, contenu_chap VARCHAR(255) NOT NULL, type_contenu VARCHAR(255) NOT NULL, cours_id INT NOT NULL, INDEX IDX_508679FC7ECF78B0 (cours_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE cours (id INT AUTO_INCREMENT NOT NULL, titre_cours VARCHAR(255) NOT NULL, niv_cours VARCHAR(100) NOT NULL, matiere_cours VARCHAR(100) NOT NULL, langue_cours VARCHAR(100) NOT NULL, description LONGTEXT DEFAULT NULL, createur_id INT NOT NULL, INDEX IDX_FDCA8C9C73A201E5 (createur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE demande_bourse (id INT AUTO_INCREMENT NOT NULL, date_demande DATETIME NOT NULL, niveau_etudes VARCHAR(255) NOT NULL, statut VARCHAR(255) NOT NULL, lettre_motivation VARCHAR(255) NOT NULL, note LONGTEXT DEFAULT NULL, etudiant_id INT NOT NULL, bourse_id INT NOT NULL, INDEX IDX_9F86FAF3DDEAB1A3 (etudiant_id), INDEX IDX_9F86FAF34E67DDD1 (bourse_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, type VARCHAR(50) NOT NULL, lien_webinaire VARCHAR(500) DEFAULT NULL, niveau VARCHAR(50) DEFAULT NULL, date DATE NOT NULL, heure_deb TIME NOT NULL, heure_fin TIME NOT NULL, date_creation DATETIME NOT NULL, image VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE event_inscription (id INT AUTO_INCREMENT NOT NULL, date_inscription DATETIME NOT NULL, statut VARCHAR(255) NOT NULL, note LONGTEXT DEFAULT NULL, participant_id INT NOT NULL, event_id INT NOT NULL, INDEX IDX_F51925569D1C3019 (participant_id), INDEX IDX_F519255671F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE historique (id INT AUTO_INCREMENT NOT NULL, entity_type VARCHAR(50) NOT NULL, action VARCHAR(20) NOT NULL, entity_id INT DEFAULT NULL, description LONGTEXT NOT NULL, actor_identifier VARCHAR(180) DEFAULT NULL, created_at DATETIME NOT NULL, actor_id INT DEFAULT NULL, INDEX IDX_EDBFD5EC10DAF24A (actor_id), INDEX idx_historique_created_at (created_at), INDEX idx_historique_entity_type (entity_type), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE inscription (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, cours_id INT NOT NULL, INDEX IDX_5E90F6D6A76ED395 (user_id), INDEX IDX_5E90F6D67ECF78B0 (cours_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, contenu LONGTEXT NOT NULL, date_publication DATETIME NOT NULL, auteur_id INT NOT NULL, sujet_id INT NOT NULL, INDEX IDX_B6BD307F60BB6FE6 (auteur_id), INDEX IDX_B6BD307F7C4D497E (sujet_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, message LONGTEXT NOT NULL, type VARCHAR(50) NOT NULL, is_read TINYINT(1) NOT NULL, date_creation DATETIME NOT NULL, destinataire_id INT NOT NULL, sujet_id INT DEFAULT NULL, message_ref_id INT DEFAULT NULL, INDEX IDX_BF5476CAA4F84F6E (destinataire_id), INDEX IDX_BF5476CA7C4D497E (sujet_id), INDEX IDX_BF5476CA1690DCA6 (message_ref_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE question (id INT AUTO_INCREMENT NOT NULL, question VARCHAR(500) NOT NULL, points INT NOT NULL, reponses JSON NOT NULL, quiz_id INT NOT NULL, INDEX IDX_B6F7494E853CD175 (quiz_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE quiz (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, type_quiz VARCHAR(255) NOT NULL, duree INT NOT NULL, score_minimum DOUBLE PRECISION NOT NULL, cours_associe_id INT NOT NULL, INDEX IDX_A412FA92C1E2D3A5 (cours_associe_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE sujet (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, contenu LONGTEXT NOT NULL, date_creation DATETIME NOT NULL, auteur_id INT NOT NULL, INDEX IDX_2E13599D60BB6FE6 (auteur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, google_id VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, nom VARCHAR(100) NOT NULL, prenom VARCHAR(100) NOT NULL, email VARCHAR(100) NOT NULL, is_active TINYINT(1) DEFAULT 1 NOT NULL, date_inscription DATETIME DEFAULT NULL, date_derniere_connexion DATETIME DEFAULT NULL, specialite VARCHAR(255) DEFAULT NULL, experience LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE analyse_interview ADD CONSTRAINT FK_57BF7C9E3B4B2704 FOREIGN KEY (demande_bourse_id) REFERENCES demande_bourse (id)');
        $this->addSql('ALTER TABLE certification ADD CONSTRAINT FK_6C3C6D75A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE certification ADD CONSTRAINT FK_6C3C6D75853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id)');
        $this->addSql('ALTER TABLE certification_finale ADD CONSTRAINT FK_7B19D134A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE certification_finale ADD CONSTRAINT FK_7B19D134853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id)');
        $this->addSql('ALTER TABLE certification_finale ADD CONSTRAINT FK_7B19D134D78CE477 FOREIGN KEY (tentative_id) REFERENCES certification (id)');
        $this->addSql('ALTER TABLE chapitres ADD CONSTRAINT FK_508679FC7ECF78B0 FOREIGN KEY (cours_id) REFERENCES cours (id)');
        $this->addSql('ALTER TABLE cours ADD CONSTRAINT FK_FDCA8C9C73A201E5 FOREIGN KEY (createur_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE demande_bourse ADD CONSTRAINT FK_9F86FAF3DDEAB1A3 FOREIGN KEY (etudiant_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE demande_bourse ADD CONSTRAINT FK_9F86FAF34E67DDD1 FOREIGN KEY (bourse_id) REFERENCES bourse (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event_inscription ADD CONSTRAINT FK_F51925569D1C3019 FOREIGN KEY (participant_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE event_inscription ADD CONSTRAINT FK_F519255671F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE historique ADD CONSTRAINT FK_EDBFD5EC10DAF24A FOREIGN KEY (actor_id) REFERENCES `user` (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE inscription ADD CONSTRAINT FK_5E90F6D6A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE inscription ADD CONSTRAINT FK_5E90F6D67ECF78B0 FOREIGN KEY (cours_id) REFERENCES cours (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F60BB6FE6 FOREIGN KEY (auteur_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F7C4D497E FOREIGN KEY (sujet_id) REFERENCES sujet (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA4F84F6E FOREIGN KEY (destinataire_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA7C4D497E FOREIGN KEY (sujet_id) REFERENCES sujet (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA1690DCA6 FOREIGN KEY (message_ref_id) REFERENCES message (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494E853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id)');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT FK_A412FA92C1E2D3A5 FOREIGN KEY (cours_associe_id) REFERENCES cours (id)');
        $this->addSql('ALTER TABLE sujet ADD CONSTRAINT FK_2E13599D60BB6FE6 FOREIGN KEY (auteur_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE analyse_interview DROP FOREIGN KEY FK_57BF7C9E3B4B2704');
        $this->addSql('ALTER TABLE certification DROP FOREIGN KEY FK_6C3C6D75A76ED395');
        $this->addSql('ALTER TABLE certification DROP FOREIGN KEY FK_6C3C6D75853CD175');
        $this->addSql('ALTER TABLE certification_finale DROP FOREIGN KEY FK_7B19D134A76ED395');
        $this->addSql('ALTER TABLE certification_finale DROP FOREIGN KEY FK_7B19D134853CD175');
        $this->addSql('ALTER TABLE certification_finale DROP FOREIGN KEY FK_7B19D134D78CE477');
        $this->addSql('ALTER TABLE chapitres DROP FOREIGN KEY FK_508679FC7ECF78B0');
        $this->addSql('ALTER TABLE cours DROP FOREIGN KEY FK_FDCA8C9C73A201E5');
        $this->addSql('ALTER TABLE demande_bourse DROP FOREIGN KEY FK_9F86FAF3DDEAB1A3');
        $this->addSql('ALTER TABLE demande_bourse DROP FOREIGN KEY FK_9F86FAF34E67DDD1');
        $this->addSql('ALTER TABLE event_inscription DROP FOREIGN KEY FK_F51925569D1C3019');
        $this->addSql('ALTER TABLE event_inscription DROP FOREIGN KEY FK_F519255671F7E88B');
        $this->addSql('ALTER TABLE historique DROP FOREIGN KEY FK_EDBFD5EC10DAF24A');
        $this->addSql('ALTER TABLE inscription DROP FOREIGN KEY FK_5E90F6D6A76ED395');
        $this->addSql('ALTER TABLE inscription DROP FOREIGN KEY FK_5E90F6D67ECF78B0');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F60BB6FE6');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F7C4D497E');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAA4F84F6E');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA7C4D497E');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA1690DCA6');
        $this->addSql('ALTER TABLE question DROP FOREIGN KEY FK_B6F7494E853CD175');
        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY FK_A412FA92C1E2D3A5');
        $this->addSql('ALTER TABLE sujet DROP FOREIGN KEY FK_2E13599D60BB6FE6');
        $this->addSql('DROP TABLE analyse_interview');
        $this->addSql('DROP TABLE bourse');
        $this->addSql('DROP TABLE certification');
        $this->addSql('DROP TABLE certification_finale');
        $this->addSql('DROP TABLE chapitres');
        $this->addSql('DROP TABLE cours');
        $this->addSql('DROP TABLE demande_bourse');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE event_inscription');
        $this->addSql('DROP TABLE historique');
        $this->addSql('DROP TABLE inscription');
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE question');
        $this->addSql('DROP TABLE quiz');
        $this->addSql('DROP TABLE sujet');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
