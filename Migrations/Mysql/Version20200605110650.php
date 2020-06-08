<?php
namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\DBALException;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbortMigrationException;

class Version20200605110650 extends AbstractMigration
{

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'add form data model';
    }

    /**
     * @param Schema $schema
     * @return void
     * @throws AbortMigrationException
     * @throws DBALException
     */
    public function up(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on "mysql".');

        $this->addSql('CREATE TABLE punktde_form_persistence_domain_model_formdata (persistence_object_identifier VARCHAR(40) NOT NULL, formidentifier VARCHAR(255) NOT NULL, hash VARCHAR(255) NOT NULL, formdata LONGTEXT NOT NULL COMMENT \'(DC2Type:flow_json_array)\', PRIMARY KEY(persistence_object_identifier)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    /**
     * @param Schema $schema
     * @return void
     * @throws AbortMigrationException
     * @throws DBALException
     */
    public function down(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on "mysql".');

        $this->addSql('DROP TABLE punktde_form_persistence_domain_model_formdata');
    }
}
