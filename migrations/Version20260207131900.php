<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260207131900 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create fipe_price table for FIPE vehicle price data';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE fipe_price (
            id INT AUTO_INCREMENT NOT NULL,
            vehicle_code VARCHAR(50) NOT NULL,
            brand VARCHAR(100) NOT NULL,
            model VARCHAR(255) NOT NULL,
            year INT NOT NULL,
            fuel VARCHAR(50) NOT NULL,
            price NUMERIC(10, 2) NOT NULL,
            reference_month VARCHAR(7) NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE fipe_price');
    }
}
