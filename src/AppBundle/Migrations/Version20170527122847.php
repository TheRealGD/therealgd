<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170527122847 extends AbstractMigration {
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE submissions ADD edited_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE submissions ADD moderated BOOLEAN DEFAULT FALSE NOT NULL');
        $this->addSql('ALTER TABLE comments ADD edited_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE comments ADD moderated BOOLEAN DEFAULT FALSE NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE submissions DROP edited_at');
        $this->addSql('ALTER TABLE submissions DROP moderated');
        $this->addSql('ALTER TABLE comments DROP edited_at');
        $this->addSql('ALTER TABLE comments DROP moderated');
    }
}
