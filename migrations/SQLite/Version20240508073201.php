<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240508073201 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE account (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, account_type INTEGER NOT NULL, balance NUMERIC(20, 2) DEFAULT NULL, currency VARCHAR(3) NOT NULL, deactivated BOOLEAN DEFAULT NULL, unit_id INTEGER DEFAULT NULL, CONSTRAINT FK_7D3656A4F8BD700D FOREIGN KEY (unit_id) REFERENCES unit (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_7D3656A4F8BD700D ON account (unit_id)');
        $this->addSql('CREATE TABLE budget (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(100) DEFAULT NULL, budget_type INTEGER NOT NULL, start_date DATE NOT NULL, currency VARCHAR(3) DEFAULT NULL, total_budgeted NUMERIC(20, 2) DEFAULT NULL, total_actual NUMERIC(20, 2) DEFAULT NULL, left_over NUMERIC(20, 2) DEFAULT NULL, added_by_user_id INTEGER DEFAULT NULL, edited_by_user_id INTEGER DEFAULT NULL, unit_id INTEGER DEFAULT NULL, CONSTRAINT FK_73F2F77BCA792C6B FOREIGN KEY (added_by_user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_73F2F77B8883BE77 FOREIGN KEY (edited_by_user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_73F2F77BF8BD700D FOREIGN KEY (unit_id) REFERENCES unit (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_73F2F77BCA792C6B ON budget (added_by_user_id)');
        $this->addSql('CREATE INDEX IDX_73F2F77B8883BE77 ON budget (edited_by_user_id)');
        $this->addSql('CREATE INDEX IDX_73F2F77BF8BD700D ON budget (unit_id)');
        $this->addSql('CREATE TABLE budget_item (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, currency VARCHAR(3) DEFAULT NULL, budgeted NUMERIC(20, 2) DEFAULT NULL, actual NUMERIC(20, 2) DEFAULT NULL, left_over NUMERIC(20, 2) DEFAULT NULL, budget_id INTEGER DEFAULT NULL, budget_sub_category_id INTEGER DEFAULT NULL, general_category_id INTEGER DEFAULT NULL, added_by_user_id INTEGER DEFAULT NULL, edited_by_user_id INTEGER DEFAULT NULL, unit_id INTEGER DEFAULT NULL, CONSTRAINT FK_65DF274E36ABA6B8 FOREIGN KEY (budget_id) REFERENCES budget (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_65DF274E534F3A79 FOREIGN KEY (budget_sub_category_id) REFERENCES budget_sub_category (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_65DF274E953D0112 FOREIGN KEY (general_category_id) REFERENCES general_category (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_65DF274ECA792C6B FOREIGN KEY (added_by_user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_65DF274E8883BE77 FOREIGN KEY (edited_by_user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_65DF274EF8BD700D FOREIGN KEY (unit_id) REFERENCES unit (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_65DF274E36ABA6B8 ON budget_item (budget_id)');
        $this->addSql('CREATE INDEX IDX_65DF274E534F3A79 ON budget_item (budget_sub_category_id)');
        $this->addSql('CREATE INDEX IDX_65DF274E953D0112 ON budget_item (general_category_id)');
        $this->addSql('CREATE INDEX IDX_65DF274ECA792C6B ON budget_item (added_by_user_id)');
        $this->addSql('CREATE INDEX IDX_65DF274E8883BE77 ON budget_item (edited_by_user_id)');
        $this->addSql('CREATE INDEX IDX_65DF274EF8BD700D ON budget_item (unit_id)');
        $this->addSql('CREATE TABLE budget_main_category (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(100) NOT NULL, unit_id INTEGER DEFAULT NULL, CONSTRAINT FK_7DF2DB80F8BD700D FOREIGN KEY (unit_id) REFERENCES unit (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_7DF2DB80F8BD700D ON budget_main_category (unit_id)');
        $this->addSql('CREATE TABLE budget_sub_category (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(100) NOT NULL, budget_main_category_id INTEGER DEFAULT NULL, unit_id INTEGER DEFAULT NULL, CONSTRAINT FK_4741B1BBB60B5129 FOREIGN KEY (budget_main_category_id) REFERENCES budget_main_category (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_4741B1BBF8BD700D FOREIGN KEY (unit_id) REFERENCES unit (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_4741B1BBB60B5129 ON budget_sub_category (budget_main_category_id)');
        $this->addSql('CREATE INDEX IDX_4741B1BBF8BD700D ON budget_sub_category (unit_id)');
        $this->addSql('CREATE TABLE department (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(100) NOT NULL, unit_id INTEGER DEFAULT NULL, CONSTRAINT FK_CD1DE18AF8BD700D FOREIGN KEY (unit_id) REFERENCES unit (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_CD1DE18AF8BD700D ON department (unit_id)');
        $this->addSql('CREATE TABLE general_category (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(100) NOT NULL, unit_id INTEGER DEFAULT NULL, CONSTRAINT FK_51558C49F8BD700D FOREIGN KEY (unit_id) REFERENCES unit (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_51558C49F8BD700D ON general_category (unit_id)');
        $this->addSql('CREATE TABLE invoice (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, invoice_number VARCHAR(64) DEFAULT NULL, invoice_date DATE DEFAULT NULL, invoice_date_due DATE DEFAULT NULL, invoice_date_paid DATE DEFAULT NULL, description VARCHAR(2048) DEFAULT NULL, priority VARCHAR(25) DEFAULT NULL, approval_status VARCHAR(25) DEFAULT NULL, payment_status VARCHAR(25) DEFAULT NULL, currency VARCHAR(3) NOT NULL, amount NUMERIC(20, 2) NOT NULL, real_currency_paid VARCHAR(3) DEFAULT NULL, real_amount_paid NUMERIC(20, 2) DEFAULT NULL, rest_payment_total NUMERIC(20, 2) DEFAULT NULL, total_paid NUMERIC(20, 2) DEFAULT NULL, bank_fee_added BOOLEAN DEFAULT NULL, bank_fee_amount NUMERIC(20, 2) DEFAULT NULL, bank_fee_not_applicable BOOLEAN DEFAULT NULL, date_time_added DATETIME NOT NULL, date_time_edited DATETIME DEFAULT NULL, added_by_user_deleted VARCHAR(64) DEFAULT NULL, edited_by_user_deleted VARCHAR(64) DEFAULT NULL, account_id INTEGER DEFAULT NULL, budget_id INTEGER DEFAULT NULL, department_id INTEGER DEFAULT NULL, supplier_id INTEGER DEFAULT NULL, added_by_user_id INTEGER DEFAULT NULL, edited_by_user_id INTEGER DEFAULT NULL, unit_id INTEGER DEFAULT NULL, CONSTRAINT FK_906517449B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_9065174436ABA6B8 FOREIGN KEY (budget_id) REFERENCES budget (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_90651744AE80F5DF FOREIGN KEY (department_id) REFERENCES department (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_906517442ADD6D8C FOREIGN KEY (supplier_id) REFERENCES supplier (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_90651744CA792C6B FOREIGN KEY (added_by_user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_906517448883BE77 FOREIGN KEY (edited_by_user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_90651744F8BD700D FOREIGN KEY (unit_id) REFERENCES unit (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_906517449B6B5FBA ON invoice (account_id)');
        $this->addSql('CREATE INDEX IDX_9065174436ABA6B8 ON invoice (budget_id)');
        $this->addSql('CREATE INDEX IDX_90651744AE80F5DF ON invoice (department_id)');
        $this->addSql('CREATE INDEX IDX_906517442ADD6D8C ON invoice (supplier_id)');
        $this->addSql('CREATE INDEX IDX_90651744CA792C6B ON invoice (added_by_user_id)');
        $this->addSql('CREATE INDEX IDX_906517448883BE77 ON invoice (edited_by_user_id)');
        $this->addSql('CREATE INDEX IDX_90651744F8BD700D ON invoice (unit_id)');
        $this->addSql('CREATE TABLE invoice_line (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, description VARCHAR(2048) DEFAULT NULL, vat NUMERIC(4, 2) DEFAULT NULL, vat_value NUMERIC(20, 2) DEFAULT NULL, net_value NUMERIC(20, 2) DEFAULT NULL, line_total NUMERIC(20, 2) NOT NULL, invoice_id INTEGER DEFAULT NULL, budget_sub_category_id INTEGER DEFAULT NULL, general_category_id INTEGER DEFAULT NULL, CONSTRAINT FK_D3D1D6932989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_D3D1D693534F3A79 FOREIGN KEY (budget_sub_category_id) REFERENCES budget_sub_category (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_D3D1D693953D0112 FOREIGN KEY (general_category_id) REFERENCES general_category (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_D3D1D6932989F1FD ON invoice_line (invoice_id)');
        $this->addSql('CREATE INDEX IDX_D3D1D693534F3A79 ON invoice_line (budget_sub_category_id)');
        $this->addSql('CREATE INDEX IDX_D3D1D693953D0112 ON invoice_line (general_category_id)');
        $this->addSql('CREATE TABLE invoice_part_payment (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, date_paid DATE NOT NULL, currency VARCHAR(3) NOT NULL, amount NUMERIC(20, 2) NOT NULL, rest_payment_amount NUMERIC(20, 2) DEFAULT NULL, real_currency_paid VARCHAR(3) DEFAULT NULL, real_amount_paid NUMERIC(20, 2) DEFAULT NULL, bank_fee_added BOOLEAN DEFAULT NULL, bank_fee_amount NUMERIC(20, 2) DEFAULT NULL, bank_fee_not_applicable BOOLEAN DEFAULT NULL, money_returned_amount NUMERIC(20, 2) DEFAULT NULL, money_returned_date DATE DEFAULT NULL, invoice_id INTEGER DEFAULT NULL, transaction_id INTEGER DEFAULT NULL, CONSTRAINT FK_52B35C722989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_52B35C722FC0CB0F FOREIGN KEY (transaction_id) REFERENCES "transaction" (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_52B35C722989F1FD ON invoice_part_payment (invoice_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_52B35C722FC0CB0F ON invoice_part_payment (transaction_id)');
        $this->addSql('CREATE TABLE purchase (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, transaction_type INTEGER NOT NULL, date_of_purchase DATE NOT NULL, description VARCHAR(2048) DEFAULT NULL, currency VARCHAR(3) DEFAULT NULL, amount NUMERIC(20, 2) NOT NULL, real_currency_paid VARCHAR(3) DEFAULT NULL, real_amount_paid NUMERIC(20, 2) DEFAULT NULL, date_time_added DATETIME DEFAULT NULL, date_time_edited DATETIME DEFAULT NULL, added_by_user_deleted VARCHAR(64) DEFAULT NULL, edited_by_user_deleted VARCHAR(64) DEFAULT NULL, account_id INTEGER DEFAULT NULL, budget_id INTEGER DEFAULT NULL, department_id INTEGER DEFAULT NULL, supplier_id INTEGER DEFAULT NULL, added_by_user_id INTEGER DEFAULT NULL, edited_by_user_id INTEGER DEFAULT NULL, unit_id INTEGER DEFAULT NULL, CONSTRAINT FK_6117D13B9B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_6117D13B36ABA6B8 FOREIGN KEY (budget_id) REFERENCES budget (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_6117D13BAE80F5DF FOREIGN KEY (department_id) REFERENCES department (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_6117D13B2ADD6D8C FOREIGN KEY (supplier_id) REFERENCES supplier (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_6117D13BCA792C6B FOREIGN KEY (added_by_user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_6117D13B8883BE77 FOREIGN KEY (edited_by_user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_6117D13BF8BD700D FOREIGN KEY (unit_id) REFERENCES unit (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_6117D13B9B6B5FBA ON purchase (account_id)');
        $this->addSql('CREATE INDEX IDX_6117D13B36ABA6B8 ON purchase (budget_id)');
        $this->addSql('CREATE INDEX IDX_6117D13BAE80F5DF ON purchase (department_id)');
        $this->addSql('CREATE INDEX IDX_6117D13B2ADD6D8C ON purchase (supplier_id)');
        $this->addSql('CREATE INDEX IDX_6117D13BCA792C6B ON purchase (added_by_user_id)');
        $this->addSql('CREATE INDEX IDX_6117D13B8883BE77 ON purchase (edited_by_user_id)');
        $this->addSql('CREATE INDEX IDX_6117D13BF8BD700D ON purchase (unit_id)');
        $this->addSql('CREATE TABLE purchase_line (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, description VARCHAR(2048) DEFAULT NULL, vat NUMERIC(4, 2) DEFAULT NULL, vat_value NUMERIC(20, 2) DEFAULT NULL, net_value NUMERIC(20, 2) DEFAULT NULL, line_total NUMERIC(20, 2) NOT NULL, purchase_id INTEGER DEFAULT NULL, budget_sub_category_id INTEGER DEFAULT NULL, general_category_id INTEGER DEFAULT NULL, CONSTRAINT FK_A1A77C95558FBEB9 FOREIGN KEY (purchase_id) REFERENCES purchase (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_A1A77C95534F3A79 FOREIGN KEY (budget_sub_category_id) REFERENCES budget_sub_category (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_A1A77C95953D0112 FOREIGN KEY (general_category_id) REFERENCES general_category (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_A1A77C95558FBEB9 ON purchase_line (purchase_id)');
        $this->addSql('CREATE INDEX IDX_A1A77C95534F3A79 ON purchase_line (budget_sub_category_id)');
        $this->addSql('CREATE INDEX IDX_A1A77C95953D0112 ON purchase_line (general_category_id)');
        $this->addSql('CREATE TABLE supplier (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, currency VARCHAR(3) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, phone_number VARCHAR(45) DEFAULT NULL, mobile_number VARCHAR(45) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, website VARCHAR(255) DEFAULT NULL, contact_name VARCHAR(100) DEFAULT NULL, job_title VARCHAR(100) DEFAULT NULL, vat_number VARCHAR(15) DEFAULT NULL, vat_rate NUMERIC(4, 2) DEFAULT NULL, bank_account_name VARCHAR(100) DEFAULT NULL, bank_account_number VARCHAR(45) DEFAULT NULL, iban VARCHAR(45) DEFAULT NULL, sort_code VARCHAR(8) DEFAULT NULL, bic_code VARCHAR(11) DEFAULT NULL, supplier_terms CLOB DEFAULT NULL, unit_id INTEGER DEFAULT NULL, CONSTRAINT FK_9B2A6C7EF8BD700D FOREIGN KEY (unit_id) REFERENCES unit (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_9B2A6C7EF8BD700D ON supplier (unit_id)');
        $this->addSql('CREATE TABLE "transaction" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, transaction_number INTEGER NOT NULL, transaction_type INTEGER NOT NULL, description VARCHAR(2048) DEFAULT NULL, date DATE DEFAULT NULL, date_time_added DATETIME NOT NULL, date_time_edited DATETIME DEFAULT NULL, currency VARCHAR(3) NOT NULL, to_currency VARCHAR(3) DEFAULT NULL, amount NUMERIC(20, 2) NOT NULL, new_value NUMERIC(20, 2) DEFAULT NULL, amount_from_account NUMERIC(20, 2) DEFAULT NULL, real_currency_paid VARCHAR(3) DEFAULT NULL, real_amount_paid NUMERIC(20, 2) DEFAULT NULL, money_returned_date DATE DEFAULT NULL, money_returned_amount NUMERIC(20, 2) DEFAULT NULL, money_in NUMERIC(20, 2) DEFAULT NULL, money_out NUMERIC(20, 2) DEFAULT NULL, bank_fee_added BOOLEAN DEFAULT NULL, bank_fee_currency VARCHAR(3) DEFAULT NULL, bank_fee_amount NUMERIC(20, 2) DEFAULT NULL, bank_fee_not_applicable BOOLEAN DEFAULT NULL, balance_main_account NUMERIC(20, 2) DEFAULT NULL, balance_transfer_from_account NUMERIC(20, 2) DEFAULT NULL, balance_transfer_to_account NUMERIC(20, 2) DEFAULT NULL, added_by_user_deleted VARCHAR(64) DEFAULT NULL, edited_by_user_deleted VARCHAR(64) DEFAULT NULL, main_account_id INTEGER DEFAULT NULL, transfer_from_account_id INTEGER DEFAULT NULL, transfer_to_account_id INTEGER DEFAULT NULL, invoice_id INTEGER DEFAULT NULL, invoice_part_payment_id INTEGER DEFAULT NULL, purchase_id INTEGER DEFAULT NULL, transaction_id INTEGER DEFAULT NULL, added_by_user_id INTEGER DEFAULT NULL, edited_by_user_id INTEGER DEFAULT NULL, unit_id INTEGER DEFAULT NULL, CONSTRAINT FK_723705D1A3932BD9 FOREIGN KEY (main_account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_723705D1675F9B07 FOREIGN KEY (transfer_from_account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_723705D1BE7BF342 FOREIGN KEY (transfer_to_account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_723705D12989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_723705D175528932 FOREIGN KEY (invoice_part_payment_id) REFERENCES invoice_part_payment (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_723705D1558FBEB9 FOREIGN KEY (purchase_id) REFERENCES purchase (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_723705D12FC0CB0F FOREIGN KEY (transaction_id) REFERENCES "transaction" (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_723705D1CA792C6B FOREIGN KEY (added_by_user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_723705D18883BE77 FOREIGN KEY (edited_by_user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_723705D1F8BD700D FOREIGN KEY (unit_id) REFERENCES unit (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_723705D1A3932BD9 ON "transaction" (main_account_id)');
        $this->addSql('CREATE INDEX IDX_723705D1675F9B07 ON "transaction" (transfer_from_account_id)');
        $this->addSql('CREATE INDEX IDX_723705D1BE7BF342 ON "transaction" (transfer_to_account_id)');
        $this->addSql('CREATE INDEX IDX_723705D12989F1FD ON "transaction" (invoice_id)');
        $this->addSql('CREATE INDEX IDX_723705D175528932 ON "transaction" (invoice_part_payment_id)');
        $this->addSql('CREATE INDEX IDX_723705D1558FBEB9 ON "transaction" (purchase_id)');
        $this->addSql('CREATE INDEX IDX_723705D12FC0CB0F ON "transaction" (transaction_id)');
        $this->addSql('CREATE INDEX IDX_723705D1CA792C6B ON "transaction" (added_by_user_id)');
        $this->addSql('CREATE INDEX IDX_723705D18883BE77 ON "transaction" (edited_by_user_id)');
        $this->addSql('CREATE INDEX IDX_723705D1F8BD700D ON "transaction" (unit_id)');
        $this->addSql('CREATE TABLE unit (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(100) NOT NULL, image VARCHAR(255) DEFAULT NULL, logo VARCHAR(255) DEFAULT NULL, description CLOB DEFAULT NULL)');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, username VARCHAR(180) NOT NULL, username_canonical VARCHAR(180) NOT NULL, email VARCHAR(180) NOT NULL, email_canonical VARCHAR(180) NOT NULL, enabled BOOLEAN NOT NULL, salt VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, confirmation_token VARCHAR(180) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles CLOB NOT NULL --(DC2Type:array)
        , created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D64992FC23A8 ON user (username_canonical)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649A0D96FBF ON user (email_canonical)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649C05FB297 ON user (confirmation_token)');
        $this->addSql('CREATE TABLE user_unit (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER DEFAULT NULL, unit_id INTEGER DEFAULT NULL, member_type INTEGER DEFAULT NULL, status INTEGER DEFAULT NULL, CONSTRAINT FK_A63A409AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_A63A409AF8BD700D FOREIGN KEY (unit_id) REFERENCES unit (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_A63A409AA76ED395 ON user_unit (user_id)');
        $this->addSql('CREATE INDEX IDX_A63A409AF8BD700D ON user_unit (unit_id)');
        $this->addSql('CREATE TABLE messenger_messages (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, body CLOB NOT NULL, headers CLOB NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , available_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , delivered_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        )');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE account');
        $this->addSql('DROP TABLE budget');
        $this->addSql('DROP TABLE budget_item');
        $this->addSql('DROP TABLE budget_main_category');
        $this->addSql('DROP TABLE budget_sub_category');
        $this->addSql('DROP TABLE department');
        $this->addSql('DROP TABLE general_category');
        $this->addSql('DROP TABLE invoice');
        $this->addSql('DROP TABLE invoice_line');
        $this->addSql('DROP TABLE invoice_part_payment');
        $this->addSql('DROP TABLE purchase');
        $this->addSql('DROP TABLE purchase_line');
        $this->addSql('DROP TABLE supplier');
        $this->addSql('DROP TABLE "transaction"');
        $this->addSql('DROP TABLE unit');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_unit');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
