<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20180127090105 extends AbstractMigration {
    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE forums RENAME canonical_name TO normalized_name');
        $this->addSql('ALTER TABLE users RENAME canonical_username TO normalized_username');
        $this->addSql('ALTER TABLE users RENAME canonical_email TO normalized_email');
        $this->addSql('ALTER TABLE wiki_pages RENAME canonical_path TO normalized_path');
        $this->addSql('ALTER INDEX uniq_fe5e5ab85e237e06 RENAME TO forums_name_idx');
        $this->addSql('ALTER INDEX uniq_fe5e5ab8d69c0128 RENAME TO forums_normalized_name_idx');
        $this->addSql('ALTER INDEX uniq_1483a5e9f85e0677 RENAME TO users_username_idx');
        $this->addSql('ALTER INDEX uniq_1483a5e96a9af538 RENAME TO users_normalized_username_idx');
        $this->addSql('ALTER INDEX uniq_8ffedcf9b548b0f RENAME TO wiki_pages_path_idx');
        $this->addSql('ALTER INDEX uniq_8ffedcf953032d1b RENAME TO wiki_pages_normalized_path_idx');
    }

    public function down(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE forums RENAME normalized_name TO canonical_name');
        $this->addSql('ALTER TABLE users RENAME normalized_username TO canonical_username');
        $this->addSql('ALTER TABLE users RENAME normalized_email TO canonical_email');
        $this->addSql('ALTER TABLE wiki_pages RENAME normalized_path TO canonical_path');
        $this->addSql('ALTER INDEX forums_name_idx RENAME TO uniq_fe5e5ab85e237e06');
        $this->addSql('ALTER INDEX forums_normalized_name_idx RENAME TO uniq_fe5e5ab8d69c0128');
        $this->addSql('ALTER INDEX users_username_idx RENAME TO uniq_1483a5e9f85e0677');
        $this->addSql('ALTER INDEX users_normalized_username_idx RENAME TO uniq_1483a5e96a9af538');
        $this->addSql('ALTER INDEX wiki_pages_path_idx RENAME TO uniq_8ffedcf9b548b0f');
        $this->addSql('ALTER INDEX wiki_pages_normalized_path_idx RENAME TO uniq_8ffedcf953032d1b');
    }
}
