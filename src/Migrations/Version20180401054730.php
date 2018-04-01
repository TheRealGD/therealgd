<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180401054730 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE forum_configuration ADD announcement_submission_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE moderators ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE moderators ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE wiki_revisions ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE wiki_revisions ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE forum_subscriptions ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE forum_subscriptions ALTER id DROP DEFAULT');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE forum_configuration DROP announcement_submission_id');
        $this->addSql('ALTER TABLE moderators ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE moderators ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE forum_subscriptions ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE forum_subscriptions ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE wiki_revisions ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE wiki_revisions ALTER id DROP DEFAULT');
    }
}
