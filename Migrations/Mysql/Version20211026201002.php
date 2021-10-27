<?php
declare(strict_types=1);

namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211026201002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add indices to form data table';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE INDEX formdatasample ON punktde_form_persistence_domain_model_formdata (formIdentifier, hash)');
        $this->addSql('CREATE INDEX dimensionshash ON punktde_form_persistence_domain_model_formdata (dimensionshash)');
        $this->addSql('CREATE INDEX sitename ON punktde_form_persistence_domain_model_formdata (sitename)');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX formdatasample ON punktde_form_persistence_domain_model_formdata');
        $this->addSql('DROP INDEX dimensionshash ON punktde_form_persistence_domain_model_formdata');
        $this->addSql('DROP INDEX sitename ON punktde_form_persistence_domain_model_formdata');
    }
}
