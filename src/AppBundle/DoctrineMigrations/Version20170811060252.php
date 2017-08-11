<?php

namespace Raddit\AppBundle\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170811060252 extends AbstractMigration {
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE moderators_id_seq CASCADE');
        $this->addSql('ALTER TABLE moderators ALTER COLUMN id SET DATA TYPE UUID USING (MD5(RANDOM()::TEXT)::UUID)');
        $this->addSql('CREATE UNIQUE INDEX moderator_forum_user_idx ON moderators (forum_id, user_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE moderators_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('DROP INDEX moderator_forum_user_idx');
        $this->addSql('ALTER TABLE moderators ALTER COLUMN id SET DATA TYPE BIGINT USING (nextval(\'moderators_id_seq\'))');
    }
}
