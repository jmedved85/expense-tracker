<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240508133710 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE comment (id INT AUTO_INCREMENT NOT NULL, datetime DATETIME NOT NULL, message LONGTEXT DEFAULT NULL, added_by_user_id INT DEFAULT NULL, added_by_user_deleted VARCHAR(64) DEFAULT NULL, invoice_id INT DEFAULT NULL, purchase_id INT DEFAULT NULL, supplier_id INT DEFAULT NULL, budget_item_id INT DEFAULT NULL, unit_id INT DEFAULT NULL, INDEX IDX_9474526CCA792C6B (added_by_user_id), INDEX IDX_9474526C2989F1FD (invoice_id), INDEX IDX_9474526C558FBEB9 (purchase_id), INDEX IDX_9474526C2ADD6D8C (supplier_id), INDEX IDX_9474526CD0EC18BF (budget_item_id), INDEX IDX_9474526CF8BD700D (unit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CCA792C6B FOREIGN KEY (added_by_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C2989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C558FBEB9 FOREIGN KEY (purchase_id) REFERENCES purchase (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C2ADD6D8C FOREIGN KEY (supplier_id) REFERENCES supplier (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CD0EC18BF FOREIGN KEY (budget_item_id) REFERENCES budget_item (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CF8BD700D FOREIGN KEY (unit_id) REFERENCES unit (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CCA792C6B');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C2989F1FD');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C558FBEB9');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C2ADD6D8C');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CD0EC18BF');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CF8BD700D');
        $this->addSql('DROP TABLE comment');
    }
}
