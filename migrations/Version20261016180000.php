<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261016180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create adoption_request_event table and backfill the timeline';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE adoption_request_event (id INT AUTO_INCREMENT NOT NULL, adoption_request_id INT NOT NULL, author_id INT DEFAULT NULL, status VARCHAR(30) NOT NULL, note LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, INDEX IDX_77728161ECFD9D75 (adoption_request_id), INDEX IDX_77728161F675F31B (author_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE adoption_request_event ADD CONSTRAINT FK_77728161ECFD9D75 FOREIGN KEY (adoption_request_id) REFERENCES adoption_request (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE adoption_request_event ADD CONSTRAINT FK_77728161F675F31B FOREIGN KEY (author_id) REFERENCES user (id) ON DELETE SET NULL');

        $this->addSql("INSERT INTO adoption_request_event (adoption_request_id, author_id, status, note, created_at) SELECT id, user_id, 'pending', NULL, created_at FROM adoption_request");
        $this->addSql("INSERT INTO adoption_request_event (adoption_request_id, author_id, status, note, created_at) SELECT id, processed_by_id, status, NULL, COALESCE(updated_at, created_at) FROM adoption_request WHERE status <> 'pending'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adoption_request_event DROP FOREIGN KEY FK_77728161ECFD9D75');
        $this->addSql('ALTER TABLE adoption_request_event DROP FOREIGN KEY FK_77728161F675F31B');
        $this->addSql('DROP TABLE adoption_request_event');
    }
}
