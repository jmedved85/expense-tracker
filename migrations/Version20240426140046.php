<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240426140046 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE purchase_line (id INT AUTO_INCREMENT NOT NULL, description VARCHAR(2048) DEFAULT NULL, vat NUMERIC(4, 2) DEFAULT NULL, vat_value NUMERIC(20, 2) DEFAULT NULL, net_value NUMERIC(20, 2) DEFAULT NULL, line_total NUMERIC(20, 2) NOT NULL, purchase_id INT DEFAULT NULL, budget_sub_category_id INT DEFAULT NULL, general_category_id INT DEFAULT NULL, INDEX IDX_A1A77C95558FBEB9 (purchase_id), INDEX IDX_A1A77C95534F3A79 (budget_sub_category_id), INDEX IDX_A1A77C95953D0112 (general_category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE purchase_line ADD CONSTRAINT FK_A1A77C95558FBEB9 FOREIGN KEY (purchase_id) REFERENCES purchase (id)');
        $this->addSql('ALTER TABLE purchase_line ADD CONSTRAINT FK_A1A77C95534F3A79 FOREIGN KEY (budget_sub_category_id) REFERENCES budget_sub_category (id)');
        $this->addSql('ALTER TABLE purchase_line ADD CONSTRAINT FK_A1A77C95953D0112 FOREIGN KEY (general_category_id) REFERENCES general_category (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE purchase_line DROP FOREIGN KEY FK_A1A77C95558FBEB9');
        $this->addSql('ALTER TABLE purchase_line DROP FOREIGN KEY FK_A1A77C95534F3A79');
        $this->addSql('ALTER TABLE purchase_line DROP FOREIGN KEY FK_A1A77C95953D0112');
        $this->addSql('DROP TABLE purchase_line');
    }
}
