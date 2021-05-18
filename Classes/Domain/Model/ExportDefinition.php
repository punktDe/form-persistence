<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Domain\Model;

/*
 *  (c) 2021 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Flow\Entity
 */
class ExportDefinition
{
    /**
     * @var string
     */
    protected $label;

    /**
     * @var string
     */
    protected $exporter;

    /**
     * @var array
     * @ORM\Column(type="flow_json_array")
     */
    protected $definition;
}
