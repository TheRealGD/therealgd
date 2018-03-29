<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180329172031 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE user_group_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE user_group (id BIGINT NOT NULL, name TEXT NOT NULL, normalized_name TEXT NOT NULL, title TEXT NOT NULL, display_title BOOLEAN DEFAULT \'false\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX user_group_name_idx ON user_group (name)');
        $this->addSql('CREATE UNIQUE INDEX user_group_normalized_name_idx ON user_group (normalized_name)');
        $this->addSql('ALTER TABLE users ADD group_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E9FE54D947 FOREIGN KEY (group_id) REFERENCES user_group (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_1483A5E9FE54D947 ON users (group_id)');
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

        $this->addSql('ALTER TABLE users DROP CONSTRAINT FK_1483A5E9FE54D947');
        $this->addSql('DROP SEQUENCE user_group_id_seq CASCADE');
        $this->addSql('DROP TABLE user_group');
        $this->addSql('ALTER TABLE moderators ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE moderators ALTER id DROP DEFAULT');
        $this->addSql('DROP INDEX IDX_1483A5E9FE54D947');
        $this->addSql('ALTER TABLE users DROP group_id');
        $this->addSql('ALTER TABLE forum_subscriptions ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE forum_subscriptions ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE wiki_revisions ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE wiki_revisions ALTER id DROP DEFAULT');
    }
}
