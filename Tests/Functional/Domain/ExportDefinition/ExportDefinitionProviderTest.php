<?php
namespace PunktDe\Form\Persistence\Tests\Functional\Domain\ExportDefinition;

/*
 *  (c) 2021 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use Neos\Flow\Tests\FunctionalTestCase;
use PunktDe\Form\Persistence\Domain\ExportDefinition\ExportDefinitionProvider;
use PunktDe\Form\Persistence\Domain\Model\ExportDefinition;
use PunktDe\Form\Persistence\Domain\Model\FormData;
use PunktDe\Form\Persistence\Domain\Repository\ExportDefinitionRepository;

class ExportDefinitionProviderTest extends FunctionalTestCase
{

    protected static $testablePersistenceEnabled = true;

    /**
     * @var ExportDefinitionRepository
     */
    protected $exportDefinitionRepository;

    /**
     * @var ExportDefinitionProvider
     */
    protected $exportDefinitionprovider;

    public function setUp(): void
    {
        parent::setUp();
        $this->exportDefinitionRepository = $this->objectManager->get(ExportDefinitionRepository::class);
        $this->exportDefinitionprovider = $this->objectManager->get(ExportDefinitionProvider::class);
    }

    /**
     * @test
     */
    public function findSuitableExportDefinitionsForFormData(): void
    {
        $formData = (new FormData())
            ->setFormData(['first_name' => 'Daniel', 'last_name' => 'Lienert'])
            ->setFormIdentifier('123')
            ->setHash('abc');

        $this->exportDefinitionRepository->add((new ExportDefinition())
            ->setLabel('Suitable address export')
            ->setDefinition(['first_name' => 'firstName', 'last_name' => 'lastName'])
            ->setExporter('csv'));

        $this->exportDefinitionRepository->add((new ExportDefinition())
            ->setLabel('Suitable firstName export')
            ->setDefinition(['first_name' => 'firstName'])
            ->setExporter('csv'));

        $this->exportDefinitionRepository->add((new ExportDefinition())
            ->setLabel('Not suitable Product Export')
            ->setDefinition(['product' => 'Neos'])
            ->setExporter('csv'));

        $this->persistenceManager->persistAll();

        $this->exportDefinitionprovider->initializeObject();
        $suitableExportDefinitions = $this->exportDefinitionprovider->findSuitableExportDefinitionsForFormData($formData);

        // expected 3, 2 dynamic, one static
        self::assertCount(3, $suitableExportDefinitions);
    }
}
