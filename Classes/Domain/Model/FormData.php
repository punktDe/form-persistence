<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Domain\Model;

/*
 *  (c) 2020 punkt.de GmbH - Karlsruhe, Germany - https://punkt.de
 *  All rights reserved.
 */

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Entity
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
     * @ORM\Column(type="flow_json_array")
     * @var array
     */
    protected $formData;

    public function getFieldNames(): array
    {
        return array_keys($this->formData);
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

    /**
     * @param array $formData
     * @return FormData
     */
    public function setFormData(array $formData): FormData
    {
        $this->formData = $formData;
        return $this;
    }
}
