<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180407000024 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE rate_limits_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE naut_bot (forum_id BIGINT NOT NULL, deep VARCHAR(255) DEFAULT NULL, shallow VARCHAR(255) DEFAULT NULL, enabled BOOLEAN NOT NULL, PRIMARY KEY(forum_id))');
        $this->addSql('CREATE TABLE rate_limits (id BIGINT NOT NULL, group_id BIGINT DEFAULT NULL, forum_id BIGINT DEFAULT NULL, rate INT NOT NULL, block BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F43409D0FE54D947 ON rate_limits (group_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F43409D029CCBAD0 ON rate_limits (forum_id)');
        $this->addSql('CREATE UNIQUE INDEX rate_limit_idx ON rate_limits (group_id, forum_id)');
        $this->addSql('ALTER TABLE naut_bot ADD CONSTRAINT FK_B70F323B29CCBAD0 FOREIGN KEY (forum_id) REFERENCES forums (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE rate_limits ADD CONSTRAINT FK_F43409D0FE54D947 FOREIGN KEY (group_id) REFERENCES user_group (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE rate_limits ADD CONSTRAINT FK_F43409D029CCBAD0 FOREIGN KEY (forum_id) REFERENCES forums (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE rate_limits_id_seq CASCADE');
        $this->addSql('DROP TABLE naut_bot');
        $this->addSql('DROP TABLE rate_limits');
    }
}
