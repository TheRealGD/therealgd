<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20180325060258 extends AbstractMigration {
    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE forum_webhooks (id UUID NOT NULL, forum_id BIGINT NOT NULL, event TEXT NOT NULL, url TEXT NOT NULL, secret_token TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_BE7FC23A29CCBAD0 ON forum_webhooks (forum_id)');
        $this->addSql('COMMENT ON COLUMN forum_webhooks.id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE forum_webhooks ADD CONSTRAINT FK_BE7FC23A29CCBAD0 FOREIGN KEY (forum_id) REFERENCES forums (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE forum_webhooks');
    }
}
