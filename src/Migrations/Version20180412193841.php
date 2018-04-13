<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180412193841 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE report_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE report_entry_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE report (id BIGINT NOT NULL, forum_id BIGINT DEFAULT NULL, submission_id BIGINT DEFAULT NULL, comment_id BIGINT DEFAULT NULL, is_resolved BOOLEAN DEFAULT \'false\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C42F778429CCBAD0 ON report (forum_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C42F7784E1FD4933 ON report (submission_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C42F7784F8697D13 ON report (comment_id)');
        $this->addSql('CREATE TABLE report_entry (id BIGINT NOT NULL, report_id BIGINT DEFAULT NULL, user_id BIGINT DEFAULT NULL, body TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_13D9A094BD2A4C0 ON report_entry (report_id)');
        $this->addSql('CREATE INDEX IDX_13D9A09A76ED395 ON report_entry (user_id)');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F778429CCBAD0 FOREIGN KEY (forum_id) REFERENCES forums (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F7784E1FD4933 FOREIGN KEY (submission_id) REFERENCES submissions (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F7784F8697D13 FOREIGN KEY (comment_id) REFERENCES comments (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report_entry ADD CONSTRAINT FK_13D9A094BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report_entry ADD CONSTRAINT FK_13D9A09A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE report_entry DROP CONSTRAINT FK_13D9A094BD2A4C0');
        $this->addSql('DROP SEQUENCE report_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE report_entry_id_seq CASCADE');
        $this->addSql('DROP TABLE report');
        $this->addSql('DROP TABLE report_entry');
        $this->addSql('ALTER TABLE moderators ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE moderators ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE forum_subscriptions ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE forum_subscriptions ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE wiki_revisions ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE wiki_revisions ALTER id DROP DEFAULT');
    }
}
