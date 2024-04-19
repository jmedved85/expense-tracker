<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240419084101 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE budget (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) DEFAULT NULL, budget_type INT NOT NULL, start_date DATE NOT NULL, total_budgeted NUMERIC(20, 2) DEFAULT NULL, total_actual NUMERIC(20, 2) DEFAULT NULL, left_over NUMERIC(20, 2) DEFAULT NULL, added_by_user_id INT DEFAULT NULL, edited_by_user_id INT DEFAULT NULL, INDEX IDX_73F2F77BCA792C6B (added_by_user_id), INDEX IDX_73F2F77B8883BE77 (edited_by_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE budget_item (id INT AUTO_INCREMENT NOT NULL, currency VARCHAR(3) DEFAULT NULL, budgeted NUMERIC(20, 2) DEFAULT NULL, actual NUMERIC(20, 2) DEFAULT NULL, budget_id INT DEFAULT NULL, budget_sub_category_id INT DEFAULT NULL, general_category_id INT DEFAULT NULL, added_by_user_id INT DEFAULT NULL, edited_by_user_id INT DEFAULT NULL, INDEX IDX_65DF274E36ABA6B8 (budget_id), INDEX IDX_65DF274E534F3A79 (budget_sub_category_id), INDEX IDX_65DF274E953D0112 (general_category_id), INDEX IDX_65DF274ECA792C6B (added_by_user_id), INDEX IDX_65DF274E8883BE77 (edited_by_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE budget ADD CONSTRAINT FK_73F2F77BCA792C6B FOREIGN KEY (added_by_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE budget ADD CONSTRAINT FK_73F2F77B8883BE77 FOREIGN KEY (edited_by_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE budget_item ADD CONSTRAINT FK_65DF274E36ABA6B8 FOREIGN KEY (budget_id) REFERENCES budget (id)');
        $this->addSql('ALTER TABLE budget_item ADD CONSTRAINT FK_65DF274E534F3A79 FOREIGN KEY (budget_sub_category_id) REFERENCES budget_sub_category (id)');
        $this->addSql('ALTER TABLE budget_item ADD CONSTRAINT FK_65DF274E953D0112 FOREIGN KEY (general_category_id) REFERENCES general_category (id)');
        $this->addSql('ALTER TABLE budget_item ADD CONSTRAINT FK_65DF274ECA792C6B FOREIGN KEY (added_by_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE budget_item ADD CONSTRAINT FK_65DF274E8883BE77 FOREIGN KEY (edited_by_user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE budget DROP FOREIGN KEY FK_73F2F77BCA792C6B');
        $this->addSql('ALTER TABLE budget DROP FOREIGN KEY FK_73F2F77B8883BE77');
        $this->addSql('ALTER TABLE budget_item DROP FOREIGN KEY FK_65DF274E36ABA6B8');
        $this->addSql('ALTER TABLE budget_item DROP FOREIGN KEY FK_65DF274E534F3A79');
        $this->addSql('ALTER TABLE budget_item DROP FOREIGN KEY FK_65DF274E953D0112');
        $this->addSql('ALTER TABLE budget_item DROP FOREIGN KEY FK_65DF274ECA792C6B');
        $this->addSql('ALTER TABLE budget_item DROP FOREIGN KEY FK_65DF274E8883BE77');
        $this->addSql('DROP TABLE budget');
        $this->addSql('DROP TABLE budget_item');
    }
}
