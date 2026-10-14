<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261014110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add a photo column to user';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD photo VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP photo');
    }
}
