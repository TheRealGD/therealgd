<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170508120624 extends AbstractMigration {
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE submissions ADD ranking BIGINT');
        $this->addSql('WITH cte AS (SELECT s.id, EXTRACT(EPOCH FROM s.timestamp) + GREATEST(LEAST(1800 * (COUNT(uv) - COUNT(dv)), 28800), 0) AS ranking FROM submissions s LEFT JOIN submission_votes uv ON (s.id = uv.submission_id AND uv.upvote) LEFT JOIN submission_votes dv ON (s.id = dv.submission_id AND NOT dv.upvote) GROUP BY s.id) UPDATE submissions s SET ranking = (SELECT ranking FROM cte WHERE cte.id = s.id)');
        $this->addSql('ALTER TABLE submissions ALTER ranking SET NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE submissions DROP ranking');
    }
}
