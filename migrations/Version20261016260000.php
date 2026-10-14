<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261016260000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add last_seen_at column to user for the messaging online status';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD last_seen_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP last_seen_at');
    }
}
