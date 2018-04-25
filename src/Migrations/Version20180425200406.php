<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20180425200406 extends AbstractMigration
{
    public function up(Schema $schema)
    {
      $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

      $this->addSql("INSERT INTO forums (id, name, title, sidebar, created, normalized_name) SELECT NEXTVAL('forums_id_seq'), 'announcements', 'announcements', '', current_timestamp, 'announcements' WHERE NOT EXISTS (SELECT 1 FROM FORUMS WHERE NAME = 'announcements');");
    }

    public function down(Schema $schema)
    {
      $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

      $this->addSql("DELETE FROM forums WHERE id IN (SELECT id FROM forums WHERE name='announcements' LIMIT 1);");
    }
}
