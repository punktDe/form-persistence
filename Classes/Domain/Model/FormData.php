<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Domain\Model;

/*
 *  (c) 2020-2021 punkt.de GmbH - Karlsruhe, Germany - https://punkt.de
 *  All rights reserved.
 */

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;
use PunktDe\Form\Persistence\Domain\ExportDefinition\ExportDefinitionInterface;
use PunktDe\Form\Persistence\Domain\Processors\ProcessorChain;
use PunktDe\Form\Persistence\Utility;

/**
 * @Flow\Entity
 *
 * @ORM\Table(
 *    indexes={
 *      @ORM\Index(name="formdatasample",columns={"formIdentifier","hash"}),
 *      @ORM\Index(name="dimensions_hash",columns={"dimensionshash"}),
 *      @ORM\Index(name="sitename",columns={"sitename"})
 *    }
 * )
 */
class FormData
{
    /**
     * @var string
     */
    protected $formIdentifier;

    /**
     * @var string
     */
    protected $hash;

    /**
     * @var \DateTime
     */
    protected $date;

    /**
     * @var string
     */
    protected $siteName = '';

    /**
     * @ORM\Column(type="flow_json_array")
     * @var array
     */
    protected $contentDimensions = [];

    /**
     * MD5 hash of the content dimensions
     * The hash is generated in buildDimensionValues().
     *
     * @var string
     * @ORM\Column(length=32)
     */
    protected $dimensionsHash = '';

    /**
     * @ORM\Column(type="flow_json_array")
     * @var array
     */
    protected $formData = [];

    /**
     * @Flow\Inject
     * @var ProcessorChain
     */
    protected $processorChain;


    public function getFieldNames(): array
    {
        return array_keys($this->formData);
    }

    public function getProcessedFieldNames(): array
    {
        return array_keys($this->getProcessedFormData());
    }

    /**
     * @return string
     */
    public function getFormIdentifier(): string
    {
        return $this->formIdentifier;
    }

    /**
     * @param string $formIdentifier
     * @return FormData
     */
    public function setFormIdentifier(string $formIdentifier): FormData
    {
        $this->formIdentifier = $formIdentifier;
        return $this;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     * @return FormData
     */
    public function setHash(string $hash): FormData
    {
        $this->hash = $hash;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDate(): \DateTime
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     * @return FormData
     */
    public function setDate(\DateTime $date): FormData
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return array
     */
    public function getFormData(): array
    {
        return $this->formData;
    }

    public function getProcessedFormData(?ExportDefinitionInterface $exportDefinition = null): array
    {
        return $this->processorChain->convertFormData($this, $this->formData, $exportDefinition);
    }

    /**
     * @param array $formData
     * @return FormData
     */
    public function setFormData(array $formData): FormData
    {
        $this->formData = $formData;
        return $this;
    }

    /**
     * @return string
     */
    public function getSiteName(): string
    {
        return $this->siteName;
    }

    /**
     * @param string $siteName
     * @return FormData
     */
    public function setSiteName(string $siteName): FormData
    {
        $this->siteName = $siteName;
        return $this;
    }

    /**
     * @param array $contentDimensions
     * @return FormData
     */
    public function setContentDimensions(array $contentDimensions): FormData
    {
        $this->contentDimensions = $contentDimensions;
        $this->dimensionsHash = Utility::sortDimensionValueArrayAndReturnDimensionsHash($contentDimensions);
        return $this;
    }

    /**
     * @return array
     */
    public function getContentDimensions(): array
    {
        return $this->contentDimensions;
    }

    /**
     * @return string
     */
    public function getDimensionsHash(): string
    {
        return $this->dimensionsHash;
    }
}
