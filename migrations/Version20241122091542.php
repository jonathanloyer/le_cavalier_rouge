<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241122091542 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64938C751C4');
        $this->addSql('CREATE TABLE player_role (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, role_name VARCHAR(255) NOT NULL, INDEX IDX_F573DA59A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE player_role ADD CONSTRAINT FK_F573DA59A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP INDEX IDX_8D93D64938C751C4 ON user');
        $this->addSql('ALTER TABLE user ADD roles JSON NOT NULL COMMENT \'(DC2Type:json)\', DROP roles_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE role (id INT AUTO_INCREMENT NOT NULL, role_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE player_role DROP FOREIGN KEY FK_F573DA59A76ED395');
        $this->addSql('DROP TABLE player_role');
        $this->addSql('ALTER TABLE user ADD roles_id INT DEFAULT NULL, DROP roles');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64938C751C4 FOREIGN KEY (roles_id) REFERENCES role (id)');
        $this->addSql('CREATE INDEX IDX_8D93D64938C751C4 ON user (roles_id)');
    }
}
