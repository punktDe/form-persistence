<?php

declare(strict_types=1);

namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210609062803 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE punktde_form_persistence_domain_model_exportdefinition CHANGE exporter exporter VARCHAR(255) NOT NULL, CHANGE filenamepattern filenamepattern VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE punktde_form_persistence_domain_model_formdata ADD sitename VARCHAR(255) NOT NULL, ADD contentdimensions LONGTEXT NOT NULL COMMENT \'(DC2Type:flow_json_array)\'');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE punktde_form_persistence_domain_model_exportdefinition CHANGE exporter exporter VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE filenamepattern filenamepattern VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE punktde_form_persistence_domain_model_formdata DROP sitename, DROP contentdimensions');
    }
}
