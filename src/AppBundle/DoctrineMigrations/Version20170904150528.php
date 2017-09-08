<?php

namespace Raddit\AppBundle\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170904150528 extends AbstractMigration {
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE user_blocks (id UUID NOT NULL, blocker_id BIGINT NOT NULL, blocked_id BIGINT NOT NULL, comment TEXT DEFAULT NULL, timestamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_ABBF8E45548D5975 ON user_blocks (blocker_id)');
        $this->addSql('CREATE INDEX IDX_ABBF8E4521FF5136 ON user_blocks (blocked_id)');
        $this->addSql('CREATE UNIQUE INDEX user_blocks_blocker_blocked_idx ON user_blocks (blocker_id, blocked_id)');
        $this->addSql('COMMENT ON COLUMN user_blocks.id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE user_blocks ADD CONSTRAINT FK_ABBF8E45548D5975 FOREIGN KEY (blocker_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_blocks ADD CONSTRAINT FK_ABBF8E4521FF5136 FOREIGN KEY (blocked_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE user_blocks');
    }
}
