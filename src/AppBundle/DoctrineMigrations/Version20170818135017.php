<?php

namespace Raddit\AppBundle\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170818135017 extends AbstractMigration {
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE themes (id UUID NOT NULL, author_id BIGINT NOT NULL, name TEXT NOT NULL, common_css TEXT DEFAULT NULL, day_css TEXT DEFAULT NULL, night_css TEXT DEFAULT NULL, append_to_default_style BOOLEAN NOT NULL, last_modified TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_154232DEF675F31B ON themes (author_id)');
        $this->addSql('CREATE UNIQUE INDEX themes_author_name_idx ON themes (author_id, name)');
        $this->addSql('COMMENT ON COLUMN themes.id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE themes ADD CONSTRAINT FK_154232DEF675F31B FOREIGN KEY (author_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE forums ADD theme_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN forums.theme_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE INDEX IDX_FE5E5AB859027487 ON forums (theme_id)');
        $this->addSql('ALTER TABLE forums ADD CONSTRAINT FK_FE5E5AB859027487 FOREIGN KEY (theme_id) REFERENCES themes (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('INSERT INTO themes (id, name, author_id, day_css, append_to_default_style, last_modified) SELECT MD5(RANDOM()::TEXT)::UUID, name, user_id, css, append_to_default_style, timestamp FROM stylesheets WHERE NOT night_friendly');
        $this->addSql('INSERT INTO themes (id, name, author_id, night_css, append_to_default_style, last_modified) SELECT MD5(RANDOM()::TEXT)::UUID, name, user_id, css, append_to_default_style, timestamp FROM stylesheets WHERE night_friendly');
        $this->addSql('WITH cte AS (SELECT t.id AS t_id, s.id AS s_id FROM themes t JOIN stylesheets s ON (t.name = s.name AND t.author_id = s.user_id)) UPDATE forums SET theme_id = cte.t_id FROM cte WHERE stylesheet_id = cte.s_id');
        $this->addSql('DROP INDEX idx_fe5e5ab848a9533f');
        $this->addSql('DROP INDEX idx_fe5e5ab8997679ec');
        $this->addSql('ALTER TABLE forums DROP CONSTRAINT fk_fe5e5ab848a9533f');
        $this->addSql('ALTER TABLE forums DROP CONSTRAINT fk_fe5e5ab8997679ec');
        $this->addSql('ALTER TABLE forums DROP night_stylesheet_id');
        $this->addSql('ALTER TABLE forums DROP stylesheet_id');
        $this->addSql('DROP SEQUENCE stylesheets_id_seq CASCADE');
        $this->addSql('DROP TABLE stylesheets');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) {
        $this->throwIrreversibleMigrationException();
    }
}
