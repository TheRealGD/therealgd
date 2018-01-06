<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170918070116 extends AbstractMigration {
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE theme_revisions ADD parent_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN theme_revisions.parent_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE theme_revisions ADD CONSTRAINT FK_4772F808727ACA70 FOREIGN KEY (parent_id) REFERENCES theme_revisions (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_4772F808727ACA70 ON theme_revisions (parent_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE theme_revisions DROP CONSTRAINT FK_4772F808727ACA70');
        $this->addSql('DROP INDEX IDX_4772F808727ACA70');
        $this->addSql('ALTER TABLE theme_revisions DROP parent_id');
    }
}
