<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261016240000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create login_record table for the account login history';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE login_record (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, ip_address VARCHAR(45) DEFAULT NULL, created_at DATETIME NOT NULL, INDEX IDX_6C50B80BA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE login_record ADD CONSTRAINT FK_6C50B80BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE login_record DROP FOREIGN KEY FK_6C50B80BA76ED395');
        $this->addSql('DROP TABLE login_record');
    }
}
