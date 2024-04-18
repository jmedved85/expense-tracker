<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240418130502 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE budget_main_category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE budget_sub_category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, budget_main_category_id INT DEFAULT NULL, INDEX IDX_4741B1BBB60B5129 (budget_main_category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE budget_sub_category ADD CONSTRAINT FK_4741B1BBB60B5129 FOREIGN KEY (budget_main_category_id) REFERENCES budget_main_category (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE budget_sub_category DROP FOREIGN KEY FK_4741B1BBB60B5129');
        $this->addSql('DROP TABLE budget_main_category');
        $this->addSql('DROP TABLE budget_sub_category');
    }
}
