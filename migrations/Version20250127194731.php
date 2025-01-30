<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250127194731 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Corrige la compatibilité SQL avec MariaDB.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE feuille_match CHANGE joueurs joueurs JSON NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL, CHANGE ffe_id ffe_id VARCHAR(255) DEFAULT NULL, CHANGE avatar avatar VARCHAR(180) DEFAULT NULL, CHANGE code_ffe code_ffe VARCHAR(255) DEFAULT NULL');
        $this->addSql('DROP INDEX uniq_identifier_email ON user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE feuille_match CHANGE joueurs joueurs LONGTEXT NOT NULL COLLATE `utf8mb4_bin`');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT \'NULL\' COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('DROP INDEX UNIQ_8D93D649E7927C74 ON user');
        $this->addSql('CREATE UNIQUE INDEX uniq_identifier_email ON user (email)');
        $this->addSql('ALTER TABLE user CHANGE roles roles LONGTEXT NOT NULL COLLATE `utf8mb4_bin`, CHANGE code_ffe code_ffe VARCHAR(255) DEFAULT \'NULL\', CHANGE ffe_id ffe_id VARCHAR(255) DEFAULT \'NULL\', CHANGE avatar avatar VARCHAR(180) DEFAULT \'NULL\'');
    }
}
