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

    /**
     * @return string
     */
    public function getFormIdentifier(): string
    {
        return $this->formIdentifier;
    }

    /**
     * @param string $formIdentifier
     */
    public function setFormIdentifier(string $formIdentifier): void
    {
        $this->formIdentifier = $formIdentifier;
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
     */
    public function setHash(string $hash): void
    {
        $this->hash = $hash;
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
     */
    public function setFormData(array $formData): void
    {
        $this->formData = $formData;
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
     */
    public function setDate(\DateTime $date): void
    {
        $this->date = $date;
    }
}
