<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Domain\Model;

/*
 *  (c) 2021 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

/**
 * @Flow\Entitiy
 */
class ScheduledExport
{

    /**
     * @var string
     */
    protected $formIdentifier;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $exportIdentifier;

    /**
     * @return string
     */
    public function getFormIdentifier(): string
    {
        return $this->formIdentifier;
    }

    /**
     * @param string $formIdentifier
     * @return ScheduledExport
     */
    public function setFormIdentifier(string $formIdentifier): ScheduledExport
    {
        $this->formIdentifier = $formIdentifier;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return ScheduledExport
     */
    public function setEmail(string $email): ScheduledExport
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getExportIdentifier(): string
    {
        return $this->exportIdentifier;
    }

    /**
     * @param string $exportIdentifier
     * @return ScheduledExport
     */
    public function setExportIdentifier(string $exportIdentifier): ScheduledExport
    {
        $this->exportIdentifier = $exportIdentifier;
        return $this;
    }
}
