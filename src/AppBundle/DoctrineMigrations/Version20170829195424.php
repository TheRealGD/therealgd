<?php

namespace Raddit\AppBundle\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170829195424 extends AbstractMigration {
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE forum_bans (id UUID NOT NULL, forum_id BIGINT NOT NULL, user_id BIGINT NOT NULL, banned_by_id BIGINT NOT NULL, reason TEXT NOT NULL, banned BOOLEAN NOT NULL, timestamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, expires_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8ED87FB29CCBAD0 ON forum_bans (forum_id)');
        $this->addSql('CREATE INDEX IDX_8ED87FBA76ED395 ON forum_bans (user_id)');
        $this->addSql('CREATE INDEX IDX_8ED87FB386B8E7 ON forum_bans (banned_by_id)');
        $this->addSql('COMMENT ON COLUMN forum_bans.id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE forum_bans ADD CONSTRAINT FK_8ED87FB29CCBAD0 FOREIGN KEY (forum_id) REFERENCES forums (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE forum_bans ADD CONSTRAINT FK_8ED87FBA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE forum_bans ADD CONSTRAINT FK_8ED87FB386B8E7 FOREIGN KEY (banned_by_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE forum_bans');
    }
}
