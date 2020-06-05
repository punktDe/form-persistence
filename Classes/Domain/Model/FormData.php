<?php

declare(strict_types=1);

namespace PunktDe\Form\Persistence\Domain\Model;

/*
 *  (c) 2020 punkt.de GmbH - Karlsruhe, Germany - https://punkt.de
 *  All rights reserved.
 */

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Flow\Entity
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="form_identifier_hash_index", columns={"formidentifier", "hash"})})
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
     * @var string
     */
    protected $formDataJson;

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
     * @return string
     */
    public function getFormDataJson(): string
    {
        return $this->formDataJson;
    }

    /**
     * @param string $formDataJson
     */
    public function setFormDataJson(string $formDataJson): void
    {
        $this->formDataJson = $formDataJson;
    }

}
