<?php

namespace Raddit\AppBundle\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20161229113003 extends AbstractMigration {
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE comments_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE comment_votes_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE forums_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE moderators_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE submissions_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE submission_votes_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE users_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE comments (id BIGINT NOT NULL, user_id BIGINT NOT NULL, submission_id BIGINT NOT NULL, parent_id BIGINT DEFAULT NULL, raw_body TEXT NOT NULL, body TEXT NOT NULL, timestamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5F9E962AA76ED395 ON comments (user_id)');
        $this->addSql('CREATE INDEX IDX_5F9E962AE1FD4933 ON comments (submission_id)');
        $this->addSql('CREATE INDEX IDX_5F9E962A727ACA70 ON comments (parent_id)');
        $this->addSql('CREATE TABLE comment_votes (id BIGINT NOT NULL, user_id BIGINT NOT NULL, comment_id BIGINT NOT NULL, upvote BOOLEAN NOT NULL, timestamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F811E23EA76ED395 ON comment_votes (user_id)');
        $this->addSql('CREATE INDEX IDX_F811E23EF8697D13 ON comment_votes (comment_id)');
        $this->addSql('CREATE UNIQUE INDEX comment_user_vote_idx ON comment_votes (comment_id, user_id)');
        $this->addSql('CREATE TABLE forums (id BIGINT NOT NULL, name TEXT NOT NULL, title TEXT NOT NULL, description TEXT DEFAULT NULL, created TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FE5E5AB85E237E06 ON forums (name)');
        $this->addSql('CREATE TABLE moderators (id BIGINT NOT NULL, forum_id BIGINT NOT NULL, user_id BIGINT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_580D16D329CCBAD0 ON moderators (forum_id)');
        $this->addSql('CREATE INDEX IDX_580D16D3A76ED395 ON moderators (user_id)');
        $this->addSql('CREATE TABLE submissions (id BIGINT NOT NULL, forum_id BIGINT NOT NULL, user_id BIGINT NOT NULL, title TEXT NOT NULL, timestamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, submission_type VARCHAR(255) NOT NULL, url TEXT DEFAULT NULL, body TEXT DEFAULT NULL, raw_body TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3F6169F729CCBAD0 ON submissions (forum_id)');
        $this->addSql('CREATE INDEX IDX_3F6169F7A76ED395 ON submissions (user_id)');
        $this->addSql('CREATE TABLE submission_votes (id BIGINT NOT NULL, user_id BIGINT NOT NULL, submission_id BIGINT NOT NULL, upvote BOOLEAN NOT NULL, timestamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8C14006DA76ED395 ON submission_votes (user_id)');
        $this->addSql('CREATE INDEX IDX_8C14006DE1FD4933 ON submission_votes (submission_id)');
        $this->addSql('CREATE UNIQUE INDEX submission_user_vote_idx ON submission_votes (submission_id, user_id)');
        $this->addSql('CREATE TABLE users (id BIGINT NOT NULL, username TEXT NOT NULL, password TEXT NOT NULL, email TEXT NOT NULL, created TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9F85E0677 ON users (username)');
        $this->addSql('ALTER TABLE comments ADD CONSTRAINT FK_5F9E962AA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE comments ADD CONSTRAINT FK_5F9E962AE1FD4933 FOREIGN KEY (submission_id) REFERENCES submissions (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE comments ADD CONSTRAINT FK_5F9E962A727ACA70 FOREIGN KEY (parent_id) REFERENCES comments (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE comment_votes ADD CONSTRAINT FK_F811E23EA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE comment_votes ADD CONSTRAINT FK_F811E23EF8697D13 FOREIGN KEY (comment_id) REFERENCES comments (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE moderators ADD CONSTRAINT FK_580D16D329CCBAD0 FOREIGN KEY (forum_id) REFERENCES forums (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE moderators ADD CONSTRAINT FK_580D16D3A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE submissions ADD CONSTRAINT FK_3F6169F729CCBAD0 FOREIGN KEY (forum_id) REFERENCES forums (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE submissions ADD CONSTRAINT FK_3F6169F7A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE submission_votes ADD CONSTRAINT FK_8C14006DA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE submission_votes ADD CONSTRAINT FK_8C14006DE1FD4933 FOREIGN KEY (submission_id) REFERENCES submissions (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE comments DROP CONSTRAINT FK_5F9E962A727ACA70');
        $this->addSql('ALTER TABLE comment_votes DROP CONSTRAINT FK_F811E23EF8697D13');
        $this->addSql('ALTER TABLE moderators DROP CONSTRAINT FK_580D16D329CCBAD0');
        $this->addSql('ALTER TABLE submissions DROP CONSTRAINT FK_3F6169F729CCBAD0');
        $this->addSql('ALTER TABLE comments DROP CONSTRAINT FK_5F9E962AE1FD4933');
        $this->addSql('ALTER TABLE submission_votes DROP CONSTRAINT FK_8C14006DE1FD4933');
        $this->addSql('ALTER TABLE comments DROP CONSTRAINT FK_5F9E962AA76ED395');
        $this->addSql('ALTER TABLE comment_votes DROP CONSTRAINT FK_F811E23EA76ED395');
        $this->addSql('ALTER TABLE moderators DROP CONSTRAINT FK_580D16D3A76ED395');
        $this->addSql('ALTER TABLE submissions DROP CONSTRAINT FK_3F6169F7A76ED395');
        $this->addSql('ALTER TABLE submission_votes DROP CONSTRAINT FK_8C14006DA76ED395');
        $this->addSql('DROP SEQUENCE comments_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE comment_votes_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE forums_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE moderators_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE submissions_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE submission_votes_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE users_id_seq CASCADE');
        $this->addSql('DROP TABLE comments');
        $this->addSql('DROP TABLE comment_votes');
        $this->addSql('DROP TABLE forums');
        $this->addSql('DROP TABLE moderators');
        $this->addSql('DROP TABLE submissions');
        $this->addSql('DROP TABLE submission_votes');
        $this->addSql('DROP TABLE users');
    }
}
