<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261016160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create medical_record table for animal medical history';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE medical_record (id INT AUTO_INCREMENT NOT NULL, animal_id INT NOT NULL, date DATE NOT NULL, category VARCHAR(30) NOT NULL, title VARCHAR(160) NOT NULL, description LONGTEXT DEFAULT NULL, veterinarian VARCHAR(160) DEFAULT NULL, created_at DATETIME NOT NULL, INDEX IDX_F06A283E8E962C16 (animal_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE medical_record ADD CONSTRAINT FK_F06A283E8E962C16 FOREIGN KEY (animal_id) REFERENCES animal (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE medical_record DROP FOREIGN KEY FK_F06A283E8E962C16');
        $this->addSql('DROP TABLE medical_record');
    }
}
