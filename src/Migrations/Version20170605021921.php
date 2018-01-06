<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170605021921 extends AbstractMigration {
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE forum_categories_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE forum_categories (id BIGINT NOT NULL, name TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE forums ADD category_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE forums ADD CONSTRAINT FK_FE5E5AB812469DE2 FOREIGN KEY (category_id) REFERENCES forum_categories (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_FE5E5AB812469DE2 ON forums (category_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE forums DROP CONSTRAINT FK_FE5E5AB812469DE2');
        $this->addSql('DROP SEQUENCE forum_categories_id_seq CASCADE');
        $this->addSql('DROP TABLE forum_categories');
        $this->addSql('DROP INDEX IDX_FE5E5AB812469DE2');
        $this->addSql('ALTER TABLE forums DROP category_id');
    }
}
