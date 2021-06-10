<?php
declare(strict_types=1);

namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210607194130 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add siteName and contentDimensions';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('ALTER TABLE punktde_form_persistence_domain_model_formdata ADD sitename VARCHAR(255) NOT NULL, LONGTEXT NOT NULL COMMENT \'(DC2Type:flow_json_array)\'');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('ALTER TABLE punktde_form_persistence_domain_model_formdata DROP sitename, DROP contentdimensions');
    }
}
