<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20171003220323 extends AbstractMigration {
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE message_threads ALTER ip DROP NOT NULL');
        $this->addSql('ALTER TABLE message_replies ALTER ip DROP NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('UPDATE message_threads SET ip = \'0.0.0.0\' WHERE ip IS NULL');
        $this->addSql('UPDATE message_replies SET ip = \'0.0.0.0\' WHERE ip IS NULL');
        $this->addSql('ALTER TABLE message_threads ALTER ip SET NOT NULL');
        $this->addSql('ALTER TABLE message_replies ALTER ip SET NOT NULL');
    }
}
