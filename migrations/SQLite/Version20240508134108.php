<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240508134108 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE comment (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, datetime DATETIME NOT NULL, message CLOB DEFAULT NULL, added_by_user_id INTEGER DEFAULT NULL, added_by_user_deleted VARCHAR(64) DEFAULT NULL, invoice_id INTEGER DEFAULT NULL, purchase_id INTEGER DEFAULT NULL, supplier_id INTEGER DEFAULT NULL, budget_item_id INTEGER DEFAULT NULL, unit_id INTEGER DEFAULT NULL, CONSTRAINT FK_9474526CCA792C6B FOREIGN KEY (added_by_user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_9474526C2989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_9474526C558FBEB9 FOREIGN KEY (purchase_id) REFERENCES purchase (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_9474526C2ADD6D8C FOREIGN KEY (supplier_id) REFERENCES supplier (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_9474526CD0EC18BF FOREIGN KEY (budget_item_id) REFERENCES budget_item (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_9474526CF8BD700D FOREIGN KEY (unit_id) REFERENCES unit (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_9474526CCA792C6B ON comment (added_by_user_id)');
        $this->addSql('CREATE INDEX IDX_9474526C2989F1FD ON comment (invoice_id)');
        $this->addSql('CREATE INDEX IDX_9474526C558FBEB9 ON comment (purchase_id)');
        $this->addSql('CREATE INDEX IDX_9474526C2ADD6D8C ON comment (supplier_id)');
        $this->addSql('CREATE INDEX IDX_9474526CD0EC18BF ON comment (budget_item_id)');
        $this->addSql('CREATE INDEX IDX_9474526CF8BD700D ON comment (unit_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE comment');
    }
}
