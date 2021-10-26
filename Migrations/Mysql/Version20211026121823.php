<?php
declare(strict_types=1);

namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Neos\ContentRepository\Utility;

final class Version20211026121823 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Introduce the form data hash';
    }

    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Exception
     * @throws \JsonException
     */
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('ALTER TABLE punktde_form_persistence_domain_model_formdata ADD dimensionshash VARCHAR(32) NOT NULL');
        $this->addDimensionHashToExistingDimensions();
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('ALTER TABLE punktde_form_persistence_domain_model_formdata DROP dimensionshash');
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws \JsonException
     */
    protected function addDimensionHashToExistingDimensions(): void
    {
        $rows = $this->connection->fetchAllAssociative('SELECT `contentdimensions` FROM punktde_form_persistence_domain_model_formdata GROUP BY contentdimensions');
        foreach ($rows as $row) {
            $contentDimensions = json_decode($row['contentdimensions'], true, 512, JSON_THROW_ON_ERROR);
            $contentDimensionHash = Utility::sortDimensionValueArrayAndReturnDimensionsHash($contentDimensions);
            $this->addSql(
                'UPDATE punktde_form_persistence_domain_model_formdata SET `dimensionshash` = :contentDimensionHash WHERE `contentdimensions` = :contentDimensions',
                [
                    'contentDimensions' => $row['contentdimensions'],
                    'contentDimensionHash' => $contentDimensionHash,
                ]
            );
        }
    }
}
