<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170409222227 extends AbstractMigration {
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE forum_subscriptions_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE forum_subscriptions (id BIGINT NOT NULL, user_id BIGINT NOT NULL, forum_id BIGINT NOT NULL, subscribed_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_ECF780C4A76ED395 ON forum_subscriptions (user_id)');
        $this->addSql('CREATE INDEX IDX_ECF780C429CCBAD0 ON forum_subscriptions (forum_id)');
        $this->addSql('CREATE UNIQUE INDEX forum_user_idx ON forum_subscriptions (forum_id, user_id)');
        $this->addSql('ALTER TABLE forum_subscriptions ADD CONSTRAINT FK_ECF780C4A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE forum_subscriptions ADD CONSTRAINT FK_ECF780C429CCBAD0 FOREIGN KEY (forum_id) REFERENCES forums (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE forum_subscriptions_id_seq CASCADE');
        $this->addSql('DROP TABLE forum_subscriptions');
    }
}
