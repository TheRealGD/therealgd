<?php

namespace Raddit\AppBundle\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170506134048 extends AbstractMigration {
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE bans_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE bans (id BIGINT NOT NULL, user_id BIGINT DEFAULT NULL, banned_by_id BIGINT NOT NULL, ip inet NOT NULL, reason TEXT DEFAULT NULL, timestamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, expiry_date TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_CB0C272CA76ED395 ON bans (user_id)');
        $this->addSql('CREATE INDEX IDX_CB0C272C386B8E7 ON bans (banned_by_id)');
        $this->addSql('COMMENT ON COLUMN bans.ip IS \'(DC2Type:inet)\'');
        $this->addSql('ALTER TABLE bans ADD CONSTRAINT FK_CB0C272CA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bans ADD CONSTRAINT FK_CB0C272C386B8E7 FOREIGN KEY (banned_by_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE comments ADD ip inet DEFAULT \'127.0.0.1\' NOT NULL');
        $this->addSql('ALTER TABLE comments ALTER ip DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN comments.ip IS \'(DC2Type:inet)\'');
        $this->addSql('ALTER TABLE comment_votes ADD ip inet DEFAULT \'127.0.0.1\' NOT NULL');
        $this->addSql('ALTER TABLE comment_votes ALTER ip DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN comment_votes.ip IS \'(DC2Type:inet)\'');
        $this->addSql('ALTER TABLE submissions ADD ip inet DEFAULT \'127.0.0.1\' NOT NULL');
        $this->addSql('ALTER TABLE submissions ALTER ip DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN submissions.ip IS \'(DC2Type:inet)\'');
        $this->addSql('ALTER TABLE submission_votes ADD ip inet DEFAULT \'127.0.0.1\' NOT NULL');
        $this->addSql('ALTER TABLE submission_votes ALTER ip DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN submission_votes.ip IS \'(DC2Type:inet)\'');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE bans_id_seq CASCADE');
        $this->addSql('DROP TABLE bans');
        $this->addSql('ALTER TABLE comments DROP ip');
        $this->addSql('ALTER TABLE comment_votes DROP ip');
        $this->addSql('ALTER TABLE submissions DROP ip');
        $this->addSql('ALTER TABLE submission_votes DROP ip');
    }
}
