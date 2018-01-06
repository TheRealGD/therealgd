<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20171126073605 extends AbstractMigration {
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        /* @noinspection SpellCheckingInspection */
        $this->addSql('UPDATE forum_log_entries SET action_type = \'submission_lock\' WHERE action_type = \'submssion_lock\'');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        /* @noinspection SpellCheckingInspection */
        $this->addSql('UPDATE forum_log_entries SET action_type = \'submssion_lock\' WHERE action_type = \'submission_lock\'');
    }
}
