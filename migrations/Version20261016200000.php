<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261016200000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add pre-adoption questionnaire fields to adoption_request';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE adoption_request ADD housing VARCHAR(20) DEFAULT 'other' NOT NULL, ADD has_garden TINYINT(1) DEFAULT 0 NOT NULL, ADD has_other_pets TINYINT(1) DEFAULT 0 NOT NULL, ADD has_children TINYINT(1) DEFAULT 0 NOT NULL, ADD experience LONGTEXT DEFAULT NULL");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adoption_request DROP housing, DROP has_garden, DROP has_other_pets, DROP has_children, DROP experience');
    }
}
