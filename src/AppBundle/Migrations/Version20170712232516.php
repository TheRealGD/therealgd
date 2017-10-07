<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170712232516 extends AbstractMigration {
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE stylesheets_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE stylesheets (id BIGINT NOT NULL, user_id BIGINT DEFAULT NULL, name TEXT NOT NULL, css TEXT NOT NULL, append_to_default_style BOOLEAN NOT NULL, night_friendly BOOLEAN NOT NULL, timestamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_1560D2A9A76ED395 ON stylesheets (user_id)');
        $this->addSql('CREATE UNIQUE INDEX stylesheets_user_name_idx ON stylesheets (user_id, name)');
        $this->addSql('ALTER TABLE stylesheets ADD CONSTRAINT FK_1560D2A9A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE forums ADD stylesheet_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE forums ADD night_stylesheet_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE forums ADD CONSTRAINT FK_FE5E5AB8997679EC FOREIGN KEY (stylesheet_id) REFERENCES stylesheets (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE forums ADD CONSTRAINT FK_FE5E5AB848A9533F FOREIGN KEY (night_stylesheet_id) REFERENCES stylesheets (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_FE5E5AB8997679EC ON forums (stylesheet_id)');
        $this->addSql('CREATE INDEX IDX_FE5E5AB848A9533F ON forums (night_stylesheet_id)');
        $this->addSql('ALTER TABLE users ADD show_custom_stylesheets BOOLEAN DEFAULT TRUE NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE forums DROP CONSTRAINT FK_FE5E5AB8997679EC');
        $this->addSql('ALTER TABLE forums DROP CONSTRAINT FK_FE5E5AB848A9533F');
        $this->addSql('DROP SEQUENCE stylesheets_id_seq CASCADE');
        $this->addSql('DROP TABLE stylesheets');
        $this->addSql('ALTER TABLE users DROP show_custom_stylesheets');
        $this->addSql('DROP INDEX IDX_FE5E5AB8997679EC');
        $this->addSql('DROP INDEX IDX_FE5E5AB848A9533F');
        $this->addSql('ALTER TABLE forums DROP stylesheet_id');
        $this->addSql('ALTER TABLE forums DROP night_stylesheet_id');
    }
}
