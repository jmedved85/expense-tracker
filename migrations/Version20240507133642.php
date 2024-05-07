<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240507133642 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE invoice (id INT AUTO_INCREMENT NOT NULL, invoice_number VARCHAR(64) DEFAULT NULL, invoice_date DATE DEFAULT NULL, invoice_date_due DATE DEFAULT NULL, invoice_date_paid DATE DEFAULT NULL, description VARCHAR(2048) DEFAULT NULL, priority VARCHAR(25) DEFAULT NULL, approval_status VARCHAR(25) DEFAULT NULL, payment_status VARCHAR(25) DEFAULT NULL, currency VARCHAR(3) NOT NULL, amount NUMERIC(20, 2) NOT NULL, real_currency_paid VARCHAR(3) DEFAULT NULL, real_amount_paid NUMERIC(20, 2) DEFAULT NULL, rest_payment_total NUMERIC(20, 2) DEFAULT NULL, total_paid NUMERIC(20, 2) DEFAULT NULL, bank_fee_added TINYINT(1) DEFAULT NULL, bank_fee_amount NUMERIC(20, 2) DEFAULT NULL, bank_fee_not_applicable TINYINT(1) DEFAULT NULL, date_time_added DATETIME NOT NULL, date_time_edited DATETIME DEFAULT NULL, account_id INT DEFAULT NULL, budget_id INT DEFAULT NULL, department_id INT DEFAULT NULL, supplier_id INT DEFAULT NULL, added_by_user_id INT DEFAULT NULL, edited_by_user_id INT DEFAULT NULL, unit_id INT DEFAULT NULL, added_by_user_deleted VARCHAR(64) DEFAULT NULL, edited_by_user_deleted VARCHAR(64) DEFAULT NULL, INDEX IDX_906517449B6B5FBA (account_id), INDEX IDX_9065174436ABA6B8 (budget_id), INDEX IDX_90651744AE80F5DF (department_id), INDEX IDX_906517442ADD6D8C (supplier_id), INDEX IDX_90651744CA792C6B (added_by_user_id), INDEX IDX_906517448883BE77 (edited_by_user_id), INDEX IDX_90651744F8BD700D (unit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE invoice_line (id INT AUTO_INCREMENT NOT NULL, description VARCHAR(2048) DEFAULT NULL, vat NUMERIC(4, 2) DEFAULT NULL, vat_value NUMERIC(20, 2) DEFAULT NULL, net_value NUMERIC(20, 2) DEFAULT NULL, line_total NUMERIC(20, 2) NOT NULL, invoice_id INT DEFAULT NULL, budget_sub_category_id INT DEFAULT NULL, general_category_id INT DEFAULT NULL, INDEX IDX_D3D1D6932989F1FD (invoice_id), INDEX IDX_D3D1D693534F3A79 (budget_sub_category_id), INDEX IDX_D3D1D693953D0112 (general_category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE invoice_part_payment (id INT AUTO_INCREMENT NOT NULL, date_paid DATE NOT NULL, currency VARCHAR(3) NOT NULL, amount NUMERIC(20, 2) NOT NULL, rest_payment_amount NUMERIC(20, 2) DEFAULT NULL, real_currency_paid VARCHAR(3) DEFAULT NULL, real_amount_paid NUMERIC(20, 2) DEFAULT NULL, bank_fee_added TINYINT(1) DEFAULT NULL, bank_fee_amount NUMERIC(20, 2) DEFAULT NULL, bank_fee_not_applicable TINYINT(1) DEFAULT NULL, money_returned_amount NUMERIC(20, 2) DEFAULT NULL, money_returned_date DATE DEFAULT NULL, invoice_id INT DEFAULT NULL, transaction_id INT DEFAULT NULL, INDEX IDX_52B35C722989F1FD (invoice_id), UNIQUE INDEX UNIQ_52B35C722FC0CB0F (transaction_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transaction (id INT AUTO_INCREMENT NOT NULL, transaction_number INT NOT NULL, transaction_type INT NOT NULL, description VARCHAR(2048) DEFAULT NULL, date DATE DEFAULT NULL, date_time_added DATETIME NOT NULL, date_time_edited DATETIME DEFAULT NULL, currency VARCHAR(3) NOT NULL, to_currency VARCHAR(3) DEFAULT NULL, amount NUMERIC(20, 2) NOT NULL, new_value NUMERIC(20, 2) DEFAULT NULL, amount_from_account NUMERIC(20, 2) DEFAULT NULL, real_currency_paid VARCHAR(3) DEFAULT NULL, real_amount_paid NUMERIC(20, 2) DEFAULT NULL, money_returned_date DATE DEFAULT NULL, money_returned_amount NUMERIC(20, 2) DEFAULT NULL, money_in NUMERIC(20, 2) DEFAULT NULL, money_out NUMERIC(20, 2) DEFAULT NULL, bank_fee_added TINYINT(1) DEFAULT NULL, bank_fee_currency VARCHAR(3) DEFAULT NULL, bank_fee_amount NUMERIC(20, 2) DEFAULT NULL, bank_fee_not_applicable TINYINT(1) DEFAULT NULL, balance_main_account NUMERIC(20, 2) DEFAULT NULL, balance_transfer_from_account NUMERIC(20, 2) DEFAULT NULL, balance_transfer_to_account NUMERIC(20, 2) DEFAULT NULL, main_account_id INT DEFAULT NULL, transfer_from_account_id INT DEFAULT NULL, transfer_to_account_id INT DEFAULT NULL, invoice_id INT DEFAULT NULL, invoice_part_payment_id INT DEFAULT NULL, purchase_id INT DEFAULT NULL, transaction_id INT DEFAULT NULL, added_by_user_id INT DEFAULT NULL, edited_by_user_id INT DEFAULT NULL, unit_id INT DEFAULT NULL, added_by_user_deleted VARCHAR(64) DEFAULT NULL, edited_by_user_deleted VARCHAR(64) DEFAULT NULL, INDEX IDX_723705D1A3932BD9 (main_account_id), INDEX IDX_723705D1675F9B07 (transfer_from_account_id), INDEX IDX_723705D1BE7BF342 (transfer_to_account_id), INDEX IDX_723705D12989F1FD (invoice_id), INDEX IDX_723705D175528932 (invoice_part_payment_id), INDEX IDX_723705D1558FBEB9 (purchase_id), INDEX IDX_723705D12FC0CB0F (transaction_id), INDEX IDX_723705D1CA792C6B (added_by_user_id), INDEX IDX_723705D18883BE77 (edited_by_user_id), INDEX IDX_723705D1F8BD700D (unit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_906517449B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id)');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_9065174436ABA6B8 FOREIGN KEY (budget_id) REFERENCES budget (id)');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_90651744AE80F5DF FOREIGN KEY (department_id) REFERENCES department (id)');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_906517442ADD6D8C FOREIGN KEY (supplier_id) REFERENCES supplier (id)');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_90651744CA792C6B FOREIGN KEY (added_by_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_906517448883BE77 FOREIGN KEY (edited_by_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_90651744F8BD700D FOREIGN KEY (unit_id) REFERENCES unit (id)');
        $this->addSql('ALTER TABLE invoice_line ADD CONSTRAINT FK_D3D1D6932989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id)');
        $this->addSql('ALTER TABLE invoice_line ADD CONSTRAINT FK_D3D1D693534F3A79 FOREIGN KEY (budget_sub_category_id) REFERENCES budget_sub_category (id)');
        $this->addSql('ALTER TABLE invoice_line ADD CONSTRAINT FK_D3D1D693953D0112 FOREIGN KEY (general_category_id) REFERENCES general_category (id)');
        $this->addSql('ALTER TABLE invoice_part_payment ADD CONSTRAINT FK_52B35C722989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id)');
        $this->addSql('ALTER TABLE invoice_part_payment ADD CONSTRAINT FK_52B35C722FC0CB0F FOREIGN KEY (transaction_id) REFERENCES transaction (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1A3932BD9 FOREIGN KEY (main_account_id) REFERENCES account (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1675F9B07 FOREIGN KEY (transfer_from_account_id) REFERENCES account (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1BE7BF342 FOREIGN KEY (transfer_to_account_id) REFERENCES account (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D12989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D175528932 FOREIGN KEY (invoice_part_payment_id) REFERENCES invoice_part_payment (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1558FBEB9 FOREIGN KEY (purchase_id) REFERENCES purchase (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D12FC0CB0F FOREIGN KEY (transaction_id) REFERENCES transaction (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1CA792C6B FOREIGN KEY (added_by_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D18883BE77 FOREIGN KEY (edited_by_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1F8BD700D FOREIGN KEY (unit_id) REFERENCES unit (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_906517449B6B5FBA');
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_9065174436ABA6B8');
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_90651744AE80F5DF');
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_906517442ADD6D8C');
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_90651744CA792C6B');
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_906517448883BE77');
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_90651744F8BD700D');
        $this->addSql('ALTER TABLE invoice_line DROP FOREIGN KEY FK_D3D1D6932989F1FD');
        $this->addSql('ALTER TABLE invoice_line DROP FOREIGN KEY FK_D3D1D693534F3A79');
        $this->addSql('ALTER TABLE invoice_line DROP FOREIGN KEY FK_D3D1D693953D0112');
        $this->addSql('ALTER TABLE invoice_part_payment DROP FOREIGN KEY FK_52B35C722989F1FD');
        $this->addSql('ALTER TABLE invoice_part_payment DROP FOREIGN KEY FK_52B35C722FC0CB0F');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1A3932BD9');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1675F9B07');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1BE7BF342');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D12989F1FD');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D175528932');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1558FBEB9');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D12FC0CB0F');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1CA792C6B');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D18883BE77');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1F8BD700D');
        $this->addSql('DROP TABLE invoice');
        $this->addSql('DROP TABLE invoice_line');
        $this->addSql('DROP TABLE invoice_part_payment');
        $this->addSql('DROP TABLE transaction');
    }
}
