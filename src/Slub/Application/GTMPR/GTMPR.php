<?php

declare(strict_types=1);

namespace Slub\Application\GTMPR;

use ConvenientImmutability\Immutable;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class GTMPR
{
    use Immutable;

    /** @var string */
    public $repository;

    /** @var string */
    public $prIdentifier;
}
