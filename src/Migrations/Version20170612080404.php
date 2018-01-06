<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170612080404 extends AbstractMigration {
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE wiki_pages ADD canonical_path TEXT');
        $this->addSql("UPDATE wiki_pages SET canonical_path = LOWER(REPLACE(path, '-', '_'))");
        $this->addSql('ALTER TABLE wiki_pages ALTER COLUMN canonical_path SET NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8FFEDCF953032D1B ON wiki_pages (canonical_path)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP INDEX UNIQ_8FFEDCF953032D1B');
        $this->addSql('ALTER TABLE wiki_pages DROP canonical_path');
    }
}
