<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261014100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add a unique slug column to animal';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE animal ADD slug VARCHAR(160) DEFAULT NULL');
        $this->addSql("UPDATE animal SET slug = CONCAT('animal-', id) WHERE slug IS NULL");
        $this->addSql('ALTER TABLE animal MODIFY slug VARCHAR(160) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_ANIMAL_SLUG ON animal (slug)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_ANIMAL_SLUG ON animal');
        $this->addSql('ALTER TABLE animal DROP slug');
    }
}
