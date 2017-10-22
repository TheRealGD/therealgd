<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20171022222938 extends AbstractMigration {
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE forum_log_entries (id UUID NOT NULL, forum_id BIGINT NOT NULL, user_id BIGINT NOT NULL, author_id BIGINT DEFAULT NULL, submission_id BIGINT DEFAULT NULL, was_admin BOOLEAN NOT NULL, timestamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, action_type TEXT NOT NULL, title TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_130108F029CCBAD0 ON forum_log_entries (forum_id)');
        $this->addSql('CREATE INDEX IDX_130108F0A76ED395 ON forum_log_entries (user_id)');
        $this->addSql('CREATE INDEX IDX_130108F0F675F31B ON forum_log_entries (author_id)');
        $this->addSql('CREATE INDEX IDX_130108F0E1FD4933 ON forum_log_entries (submission_id)');
        $this->addSql('COMMENT ON COLUMN forum_log_entries.id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE forum_log_entries ADD CONSTRAINT FK_130108F029CCBAD0 FOREIGN KEY (forum_id) REFERENCES forums (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE forum_log_entries ADD CONSTRAINT FK_130108F0A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE forum_log_entries ADD CONSTRAINT FK_130108F0F675F31B FOREIGN KEY (author_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE forum_log_entries ADD CONSTRAINT FK_130108F0E1FD4933 FOREIGN KEY (submission_id) REFERENCES submissions (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE forum_log_entries');
    }
}
