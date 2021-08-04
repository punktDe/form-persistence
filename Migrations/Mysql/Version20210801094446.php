<?php

declare(strict_types=1);

namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210801094446 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds the scheduled export table';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE punktde_form_persistence_domain_model_scheduledexport (persistence_object_identifier VARCHAR(40) NOT NULL, formidentifier VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, exportdefinitionidentifier VARCHAR(255) NOT NULL, PRIMARY KEY(persistence_object_identifier)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('DROP TABLE punktde_form_persistence_domain_model_scheduledexport');
    }
}
