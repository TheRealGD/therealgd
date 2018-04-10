<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180409192923 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('DROP INDEX UNIQ_F43409D0FE54D947');
        $this->addSql('DROP INDEX UNIQ_F43409D029CCBAD0');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F43409D0FE54D947 ON rate_limits (group_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F43409D029CCBAD0 ON rate_limits (forum_id)');
    }
}
