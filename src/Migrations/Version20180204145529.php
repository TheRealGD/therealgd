<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20180204145529 extends AbstractMigration {
    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE forum_categories RENAME name TO title');
        $this->addSql('ALTER TABLE forum_categories ADD name TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE forum_categories ADD normalized_name TEXT DEFAULT NULL');
        $this->addSql('UPDATE forum_categories SET name = REGEXP_REPLACE(title, \'\W+\', \'\', \'g\')');
        $this->addSql('UPDATE forum_categories SET normalized_name = LOWER(name)');
        $this->addSql('ALTER TABLE forum_categories ALTER name SET NOT NULL');
        $this->addSql('ALTER TABLE forum_categories ALTER normalized_name SET NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX forum_categories_name_idx ON forum_categories (name)');
        $this->addSql('CREATE UNIQUE INDEX forum_categories_normalized_name_idx ON forum_categories (normalized_name)');
    }

    public function down(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP INDEX forum_categories_name_idx');
        $this->addSql('DROP INDEX forum_categories_normalized_name_idx');
        $this->addSql('ALTER TABLE forum_categories DROP name');
        $this->addSql('ALTER TABLE forum_categories DROP normalized_name');
        $this->addSql('ALTER TABLE forum_categories RENAME title TO name');
    }
}
