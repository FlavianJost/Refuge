<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261015120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create donation table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE donation (id INT AUTO_INCREMENT NOT NULL, amount INT NOT NULL, donor_name VARCHAR(120) DEFAULT NULL, donor_email VARCHAR(180) DEFAULT NULL, message LONGTEXT DEFAULT NULL, status VARCHAR(30) NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE donation');
    }
}
