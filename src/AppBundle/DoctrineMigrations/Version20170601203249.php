<?php

namespace Raddit\AppBundle\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170601203249 extends AbstractMigration {
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE message_threads_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE message_replies_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE message_threads (id BIGINT NOT NULL, sender_id BIGINT NOT NULL, receiver_id BIGINT NOT NULL, body TEXT NOT NULL, timestamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, ip inet NOT NULL, title TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_FF0607D1F624B39D ON message_threads (sender_id)');
        $this->addSql('CREATE INDEX IDX_FF0607D1CD53EDB6 ON message_threads (receiver_id)');
        $this->addSql('COMMENT ON COLUMN message_threads.ip IS \'(DC2Type:inet)\'');
        $this->addSql('CREATE TABLE message_replies (id BIGINT NOT NULL, sender_id BIGINT NOT NULL, thread_id BIGINT NOT NULL, body TEXT NOT NULL, timestamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, ip inet NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_30885D26F624B39D ON message_replies (sender_id)');
        $this->addSql('CREATE INDEX IDX_30885D26E2904019 ON message_replies (thread_id)');
        $this->addSql('COMMENT ON COLUMN message_replies.ip IS \'(DC2Type:inet)\'');
        $this->addSql('ALTER TABLE message_threads ADD CONSTRAINT FK_FF0607D1F624B39D FOREIGN KEY (sender_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE message_threads ADD CONSTRAINT FK_FF0607D1CD53EDB6 FOREIGN KEY (receiver_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE message_replies ADD CONSTRAINT FK_30885D26F624B39D FOREIGN KEY (sender_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE message_replies ADD CONSTRAINT FK_30885D26E2904019 FOREIGN KEY (thread_id) REFERENCES message_threads (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notifications ADD thread_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE notifications ADD reply_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE notifications ADD CONSTRAINT FK_6000B0D3E2904019 FOREIGN KEY (thread_id) REFERENCES message_threads (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notifications ADD CONSTRAINT FK_6000B0D38A0E4E7F FOREIGN KEY (reply_id) REFERENCES message_replies (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_6000B0D3E2904019 ON notifications (thread_id)');
        $this->addSql('CREATE INDEX IDX_6000B0D38A0E4E7F ON notifications (reply_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE notifications DROP CONSTRAINT FK_6000B0D3E2904019');
        $this->addSql('ALTER TABLE message_replies DROP CONSTRAINT FK_30885D26E2904019');
        $this->addSql('ALTER TABLE notifications DROP CONSTRAINT FK_6000B0D38A0E4E7F');
        $this->addSql('DROP SEQUENCE message_threads_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE message_replies_id_seq CASCADE');
        $this->addSql('DROP TABLE message_threads');
        $this->addSql('DROP TABLE message_replies');
        $this->addSql('DROP INDEX IDX_6000B0D3E2904019');
        $this->addSql('DROP INDEX IDX_6000B0D38A0E4E7F');
        $this->addSql('ALTER TABLE notifications DROP thread_id');
        $this->addSql('ALTER TABLE notifications DROP reply_id');
    }
}
