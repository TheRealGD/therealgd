<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170802214902 extends AbstractMigration {
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE forums RENAME description TO sidebar');
        $this->addSql('ALTER TABLE forums ADD description TEXT DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE forums DROP description');
        $this->addSql('ALTER TABLE forums RENAME sidebar TO description');
    }
}
