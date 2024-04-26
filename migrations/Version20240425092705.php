<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240425092705 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE purchase (id INT AUTO_INCREMENT NOT NULL, transaction_type INT NOT NULL, date_of_purchase DATE NOT NULL, description VARCHAR(2048) DEFAULT NULL, currency VARCHAR(3) DEFAULT NULL, amount NUMERIC(20, 2) NOT NULL, real_currency_paid VARCHAR(3) DEFAULT NULL, real_amount_paid NUMERIC(20, 2) DEFAULT NULL, date_time_added DATETIME DEFAULT NULL, date_time_edited DATETIME DEFAULT NULL, added_by_user_deleted VARCHAR(64) DEFAULT NULL, edited_by_user_deleted VARCHAR(64) DEFAULT NULL, account_id INT DEFAULT NULL, budget_id INT DEFAULT NULL, department_id INT DEFAULT NULL, supplier_id INT DEFAULT NULL, added_by_user_id INT DEFAULT NULL, edited_by_user_id INT DEFAULT NULL, unit_id INT DEFAULT NULL, INDEX IDX_6117D13B9B6B5FBA (account_id), INDEX IDX_6117D13B36ABA6B8 (budget_id), INDEX IDX_6117D13BAE80F5DF (department_id), INDEX IDX_6117D13B2ADD6D8C (supplier_id), INDEX IDX_6117D13BCA792C6B (added_by_user_id), INDEX IDX_6117D13B8883BE77 (edited_by_user_id), INDEX IDX_6117D13BF8BD700D (unit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE purchase ADD CONSTRAINT FK_6117D13B9B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id)');
        $this->addSql('ALTER TABLE purchase ADD CONSTRAINT FK_6117D13B36ABA6B8 FOREIGN KEY (budget_id) REFERENCES budget (id)');
        $this->addSql('ALTER TABLE purchase ADD CONSTRAINT FK_6117D13BAE80F5DF FOREIGN KEY (department_id) REFERENCES department (id)');
        $this->addSql('ALTER TABLE purchase ADD CONSTRAINT FK_6117D13B2ADD6D8C FOREIGN KEY (supplier_id) REFERENCES supplier (id)');
        $this->addSql('ALTER TABLE purchase ADD CONSTRAINT FK_6117D13BCA792C6B FOREIGN KEY (added_by_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE purchase ADD CONSTRAINT FK_6117D13B8883BE77 FOREIGN KEY (edited_by_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE purchase ADD CONSTRAINT FK_6117D13BF8BD700D FOREIGN KEY (unit_id) REFERENCES unit (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE purchase DROP FOREIGN KEY FK_6117D13B9B6B5FBA');
        $this->addSql('ALTER TABLE purchase DROP FOREIGN KEY FK_6117D13B36ABA6B8');
        $this->addSql('ALTER TABLE purchase DROP FOREIGN KEY FK_6117D13BAE80F5DF');
        $this->addSql('ALTER TABLE purchase DROP FOREIGN KEY FK_6117D13B2ADD6D8C');
        $this->addSql('ALTER TABLE purchase DROP FOREIGN KEY FK_6117D13BCA792C6B');
        $this->addSql('ALTER TABLE purchase DROP FOREIGN KEY FK_6117D13B8883BE77');
        $this->addSql('ALTER TABLE purchase DROP FOREIGN KEY FK_6117D13BF8BD700D');
        $this->addSql('DROP TABLE purchase');
    }
}
