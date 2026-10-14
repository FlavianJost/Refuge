<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261016120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create animal_photo table for the animal photo gallery';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE animal_photo (id INT AUTO_INCREMENT NOT NULL, animal_id INT NOT NULL, filename VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_35445DEC8E962C16 (animal_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE animal_photo ADD CONSTRAINT FK_35445DEC8E962C16 FOREIGN KEY (animal_id) REFERENCES animal (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE animal_photo DROP FOREIGN KEY FK_35445DEC8E962C16');
        $this->addSql('DROP TABLE animal_photo');
    }
}
