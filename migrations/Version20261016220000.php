<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261016220000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create visit_appointment table for adoption visit appointments';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE visit_appointment (id INT AUTO_INCREMENT NOT NULL, adoption_request_id INT NOT NULL, processed_by_id INT DEFAULT NULL, proposed_at DATETIME NOT NULL, status VARCHAR(30) NOT NULL, note LONGTEXT DEFAULT NULL, response LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_7AE251E4ECFD9D75 (adoption_request_id), INDEX IDX_7AE251E42FFD4FD3 (processed_by_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE visit_appointment ADD CONSTRAINT FK_7AE251E4ECFD9D75 FOREIGN KEY (adoption_request_id) REFERENCES adoption_request (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE visit_appointment ADD CONSTRAINT FK_7AE251E42FFD4FD3 FOREIGN KEY (processed_by_id) REFERENCES user (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE visit_appointment DROP FOREIGN KEY FK_7AE251E4ECFD9D75');
        $this->addSql('ALTER TABLE visit_appointment DROP FOREIGN KEY FK_7AE251E42FFD4FD3');
        $this->addSql('DROP TABLE visit_appointment');
    }
}
