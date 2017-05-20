<?php

namespace Raddit\AppBundle\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170520190400 extends AbstractMigration {
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE comments ALTER ip DROP NOT NULL');
        $this->addSql('ALTER TABLE comment_votes ALTER ip DROP NOT NULL');
        $this->addSql('ALTER TABLE submissions ALTER ip DROP NOT NULL');
        $this->addSql('ALTER TABLE submission_votes ALTER ip DROP NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql("UPDATE comments SET ip = '127.0.0.1' WHERE ip IS NULL");
        $this->addSql("UPDATE comment_votes SET ip = '127.0.0.1' WHERE ip IS NULL");
        $this->addSql("UPDATE submissions SET ip = '127.0.0.1' WHERE ip IS NULL");
        $this->addSql("UPDATE submission_votes SET ip = '127.0.0.1' WHERE ip IS NULL");
        $this->addSql('ALTER TABLE comments ALTER ip SET NOT NULL');
        $this->addSql('ALTER TABLE comment_votes ALTER ip SET NOT NULL');
        $this->addSql('ALTER TABLE submissions ALTER ip SET NOT NULL');
        $this->addSql('ALTER TABLE submission_votes ALTER ip SET NOT NULL');
    }
}
