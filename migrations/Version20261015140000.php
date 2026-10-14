<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261015140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create contact_message table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE contact_message (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, subject VARCHAR(150) NOT NULL, message LONGTEXT NOT NULL, handled TINYINT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_2C9211FEA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE contact_message ADD CONSTRAINT FK_2C9211FEA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE contact_message DROP FOREIGN KEY FK_2C9211FEA76ED395');
        $this->addSql('DROP TABLE contact_message');
    }
}
