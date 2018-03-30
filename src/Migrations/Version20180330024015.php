<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180330024015 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE forum_configuration_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE forum_configuration (id BIGINT NOT NULL, forum_id BIGINT DEFAULT NULL, announcement TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE forum_subscriptions ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE forum_subscriptions ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE wiki_revisions ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE wiki_revisions ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE moderators ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE moderators ALTER id DROP DEFAULT');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE forum_configuration_id_seq CASCADE');
        $this->addSql('DROP TABLE forum_configuration');
        $this->addSql('ALTER TABLE moderators ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE moderators ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE forum_subscriptions ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE forum_subscriptions ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE wiki_revisions ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE wiki_revisions ALTER id DROP DEFAULT');
    }
}
