<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240423084243 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_unit (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, unit_id INT DEFAULT NULL, member_type INT DEFAULT NULL, status INT DEFAULT NULL, INDEX IDX_A63A409AA76ED395 (user_id), INDEX IDX_A63A409AF8BD700D (unit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_unit ADD CONSTRAINT FK_A63A409AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_unit ADD CONSTRAINT FK_A63A409AF8BD700D FOREIGN KEY (unit_id) REFERENCES unit (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_unit DROP FOREIGN KEY FK_A63A409AA76ED395');
        $this->addSql('ALTER TABLE user_unit DROP FOREIGN KEY FK_A63A409AF8BD700D');
        $this->addSql('DROP TABLE user_unit');
    }
}
