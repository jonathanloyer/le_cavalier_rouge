<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241128194852 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE club (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE competitions (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE feuille_match (id INT AUTO_INCREMENT NOT NULL, club_a_id INT DEFAULT NULL, club_b_id INT DEFAULT NULL, creation DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', date_match DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ronde INT NOT NULL, INDEX IDX_1628A52E5F1699B8 (club_a_id), INDEX IDX_1628A52E4DA33656 (club_b_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE joueurs (id INT AUTO_INCREMENT NOT NULL, club_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_F0FD889D61190A32 (club_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE player_role (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, role_name VARCHAR(255) NOT NULL, INDEX IDX_F573DA59A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, club_id INT DEFAULT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', last_name VARCHAR(100) NOT NULL, first_name VARCHAR(100) NOT NULL, pseudo VARCHAR(255) NOT NULL, ffe_id VARCHAR(255) DEFAULT NULL, active TINYINT(1) NOT NULL, avatar VARCHAR(180) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', password VARCHAR(255) NOT NULL, INDEX IDX_8D93D64961190A32 (club_id), UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE feuille_match ADD CONSTRAINT FK_1628A52E5F1699B8 FOREIGN KEY (club_a_id) REFERENCES club (id)');
        $this->addSql('ALTER TABLE feuille_match ADD CONSTRAINT FK_1628A52E4DA33656 FOREIGN KEY (club_b_id) REFERENCES club (id)');
        $this->addSql('ALTER TABLE joueurs ADD CONSTRAINT FK_F0FD889D61190A32 FOREIGN KEY (club_id) REFERENCES club (id)');
        $this->addSql('ALTER TABLE player_role ADD CONSTRAINT FK_F573DA59A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64961190A32 FOREIGN KEY (club_id) REFERENCES club (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE feuille_match DROP FOREIGN KEY FK_1628A52E5F1699B8');
        $this->addSql('ALTER TABLE feuille_match DROP FOREIGN KEY FK_1628A52E4DA33656');
        $this->addSql('ALTER TABLE joueurs DROP FOREIGN KEY FK_F0FD889D61190A32');
        $this->addSql('ALTER TABLE player_role DROP FOREIGN KEY FK_F573DA59A76ED395');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64961190A32');
        $this->addSql('DROP TABLE club');
        $this->addSql('DROP TABLE competitions');
        $this->addSql('DROP TABLE feuille_match');
        $this->addSql('DROP TABLE joueurs');
        $this->addSql('DROP TABLE player_role');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
