<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261016140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add views counter to animal';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE animal ADD views INT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE animal DROP views');
    }
}
