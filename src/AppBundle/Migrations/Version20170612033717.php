<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170612033717 extends AbstractMigration {
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE wiki_pages_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE wiki_revisions_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE wiki_pages (id BIGINT NOT NULL, current_revision_id BIGINT DEFAULT NULL, path TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8FFEDCF9B548B0F ON wiki_pages (path)');
        $this->addSql('CREATE INDEX IDX_8FFEDCF9A32ED756 ON wiki_pages (current_revision_id)');
        $this->addSql('CREATE TABLE wiki_revisions (id BIGINT NOT NULL, page_id BIGINT NOT NULL, user_id BIGINT NOT NULL, title TEXT NOT NULL, body TEXT NOT NULL, timestamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_82813BA1C4663E4 ON wiki_revisions (page_id)');
        $this->addSql('CREATE INDEX IDX_82813BA1A76ED395 ON wiki_revisions (user_id)');
        $this->addSql('ALTER TABLE wiki_pages ADD CONSTRAINT FK_8FFEDCF9A32ED756 FOREIGN KEY (current_revision_id) REFERENCES wiki_revisions (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE wiki_revisions ADD CONSTRAINT FK_82813BA1C4663E4 FOREIGN KEY (page_id) REFERENCES wiki_pages (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE wiki_revisions ADD CONSTRAINT FK_82813BA1A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE wiki_revisions DROP CONSTRAINT FK_82813BA1C4663E4');
        $this->addSql('ALTER TABLE wiki_pages DROP CONSTRAINT FK_8FFEDCF9A32ED756');
        $this->addSql('DROP SEQUENCE wiki_pages_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE wiki_revisions_id_seq CASCADE');
        $this->addSql('DROP TABLE wiki_pages');
        $this->addSql('DROP TABLE wiki_revisions');
    }
}
