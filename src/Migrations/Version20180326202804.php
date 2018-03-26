<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180326202804 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE submissions ADD mod_thread BOOLEAN DEFAULT \'false\' NOT NULL');
        $this->addSql('ALTER TABLE forum_subscriptions ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE forum_subscriptions ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE moderators ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE moderators ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE wiki_revisions ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE wiki_revisions ALTER id DROP DEFAULT');
        $this->addSql('INSERT INTO forums (id, created, name, title, sidebar, normalized_name, description) VALUES (0, current_timestamp, \'ModForum\', \'ModForum\', \'ModForum\', \'modforum\', \'ModForum for moderation logs\')');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE moderators ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE moderators ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE forum_subscriptions ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE forum_subscriptions ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE submissions DROP mod_thread');
        $this->addSql('ALTER TABLE wiki_revisions ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE wiki_revisions ALTER id DROP DEFAULT');
    }
}
