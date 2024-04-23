<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240423082412 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE account ADD unit_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE account ADD CONSTRAINT FK_7D3656A4F8BD700D FOREIGN KEY (unit_id) REFERENCES unit (id)');
        $this->addSql('CREATE INDEX IDX_7D3656A4F8BD700D ON account (unit_id)');
        $this->addSql('ALTER TABLE budget ADD unit_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE budget ADD CONSTRAINT FK_73F2F77BF8BD700D FOREIGN KEY (unit_id) REFERENCES unit (id)');
        $this->addSql('CREATE INDEX IDX_73F2F77BF8BD700D ON budget (unit_id)');
        $this->addSql('ALTER TABLE budget_item ADD unit_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE budget_item ADD CONSTRAINT FK_65DF274EF8BD700D FOREIGN KEY (unit_id) REFERENCES unit (id)');
        $this->addSql('CREATE INDEX IDX_65DF274EF8BD700D ON budget_item (unit_id)');
        $this->addSql('ALTER TABLE budget_main_category ADD unit_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE budget_main_category ADD CONSTRAINT FK_7DF2DB80F8BD700D FOREIGN KEY (unit_id) REFERENCES unit (id)');
        $this->addSql('CREATE INDEX IDX_7DF2DB80F8BD700D ON budget_main_category (unit_id)');
        $this->addSql('ALTER TABLE budget_sub_category ADD unit_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE budget_sub_category ADD CONSTRAINT FK_4741B1BBF8BD700D FOREIGN KEY (unit_id) REFERENCES unit (id)');
        $this->addSql('CREATE INDEX IDX_4741B1BBF8BD700D ON budget_sub_category (unit_id)');
        $this->addSql('ALTER TABLE department ADD unit_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE department ADD CONSTRAINT FK_CD1DE18AF8BD700D FOREIGN KEY (unit_id) REFERENCES unit (id)');
        $this->addSql('CREATE INDEX IDX_CD1DE18AF8BD700D ON department (unit_id)');
        $this->addSql('ALTER TABLE general_category ADD unit_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE general_category ADD CONSTRAINT FK_51558C49F8BD700D FOREIGN KEY (unit_id) REFERENCES unit (id)');
        $this->addSql('CREATE INDEX IDX_51558C49F8BD700D ON general_category (unit_id)');
        $this->addSql('ALTER TABLE supplier ADD unit_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE supplier ADD CONSTRAINT FK_9B2A6C7EF8BD700D FOREIGN KEY (unit_id) REFERENCES unit (id)');
        $this->addSql('CREATE INDEX IDX_9B2A6C7EF8BD700D ON supplier (unit_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE supplier DROP FOREIGN KEY FK_9B2A6C7EF8BD700D');
        $this->addSql('DROP INDEX IDX_9B2A6C7EF8BD700D ON supplier');
        $this->addSql('ALTER TABLE supplier DROP unit_id');
        $this->addSql('ALTER TABLE account DROP FOREIGN KEY FK_7D3656A4F8BD700D');
        $this->addSql('DROP INDEX IDX_7D3656A4F8BD700D ON account');
        $this->addSql('ALTER TABLE account DROP unit_id');
        $this->addSql('ALTER TABLE budget_item DROP FOREIGN KEY FK_65DF274EF8BD700D');
        $this->addSql('DROP INDEX IDX_65DF274EF8BD700D ON budget_item');
        $this->addSql('ALTER TABLE budget_item DROP unit_id');
        $this->addSql('ALTER TABLE budget_main_category DROP FOREIGN KEY FK_7DF2DB80F8BD700D');
        $this->addSql('DROP INDEX IDX_7DF2DB80F8BD700D ON budget_main_category');
        $this->addSql('ALTER TABLE budget_main_category DROP unit_id');
        $this->addSql('ALTER TABLE budget_sub_category DROP FOREIGN KEY FK_4741B1BBF8BD700D');
        $this->addSql('DROP INDEX IDX_4741B1BBF8BD700D ON budget_sub_category');
        $this->addSql('ALTER TABLE budget_sub_category DROP unit_id');
        $this->addSql('ALTER TABLE budget DROP FOREIGN KEY FK_73F2F77BF8BD700D');
        $this->addSql('DROP INDEX IDX_73F2F77BF8BD700D ON budget');
        $this->addSql('ALTER TABLE budget DROP unit_id');
        $this->addSql('ALTER TABLE department DROP FOREIGN KEY FK_CD1DE18AF8BD700D');
        $this->addSql('DROP INDEX IDX_CD1DE18AF8BD700D ON department');
        $this->addSql('ALTER TABLE department DROP unit_id');
        $this->addSql('ALTER TABLE general_category DROP FOREIGN KEY FK_51558C49F8BD700D');
        $this->addSql('DROP INDEX IDX_51558C49F8BD700D ON general_category');
        $this->addSql('ALTER TABLE general_category DROP unit_id');
    }
}
