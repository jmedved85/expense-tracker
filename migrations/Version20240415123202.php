<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240415123202 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE supplier (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, currency VARCHAR(3) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, phone_number VARCHAR(45) DEFAULT NULL, mobile_number VARCHAR(45) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, website VARCHAR(255) DEFAULT NULL, contact_name VARCHAR(100) DEFAULT NULL, job_title VARCHAR(100) DEFAULT NULL, vat_number VARCHAR(15) DEFAULT NULL, vat_rate NUMERIC(4, 2) DEFAULT NULL, bank_account_name VARCHAR(100) DEFAULT NULL, bank_account_number VARCHAR(45) DEFAULT NULL, iban VARCHAR(45) DEFAULT NULL, sort_code VARCHAR(8) DEFAULT NULL, bic_code VARCHAR(11) DEFAULT NULL, supplier_terms LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE supplier');
    }
}
