<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261015130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create review table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE review (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, rating INT NOT NULL, comment LONGTEXT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_794381C6A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6A76ED395');
        $this->addSql('DROP TABLE review');
    }
}
