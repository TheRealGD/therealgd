<?php

namespace Raddit\AppBundle\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class Version20170101133959 extends AbstractMigration implements ContainerAwareInterface {
    use ContainerAwareTrait;

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('ALTER TABLE forums ADD canonical_name TEXT');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FE5E5AB8D69C0128 ON forums (canonical_name)');
        // Give forums with duplicate lowercase keys a name that could not have
        // been created within the software, as dash characters are not allowed.
        $this->addSql('UPDATE forums SET name = name || \'-\' || id WHERE LOWER(name) IN (SELECT lower_name FROM (SELECT LOWER(name) AS lower_name, COUNT(*) AS count FROM forums GROUP BY LOWER(name)) AS q WHERE count > 1)');
        // Now this shouldn't cause problems with the unique constraint.
        $this->addSql('UPDATE forums SET canonical_name = LOWER(name)');
        $this->addSql('ALTER TABLE forums ALTER COLUMN canonical_name SET NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP INDEX UNIQ_FE5E5AB8D69C0128');
        $this->addSql('ALTER TABLE forums DROP canonical_name');

        // Undo cases from up() where forums were renamed.
        $this->addSql('UPDATE forums SET name = REGEXP_REPLACE(name, \'-.*\', \'\')');
    }
}
