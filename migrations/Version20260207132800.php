<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260207132800 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create vehicle table for vehicle management';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE vehicle (
            id INT AUTO_INCREMENT NOT NULL,
            make VARCHAR(255) NOT NULL,
            model VARCHAR(255) NOT NULL,
            version VARCHAR(255) NOT NULL,
            image VARCHAR(255) DEFAULT NULL,
            kms INT NOT NULL,
            price DOUBLE PRECISION NOT NULL,
            year_model INT NOT NULL,
            year_fab INT NOT NULL,
            color VARCHAR(255) NOT NULL,
            fipe_code VARCHAR(20) DEFAULT NULL,
            fuel VARCHAR(50) DEFAULT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE vehicle');
    }
}
