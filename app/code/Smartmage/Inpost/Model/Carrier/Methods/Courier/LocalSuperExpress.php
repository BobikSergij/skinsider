<?php

declare(strict_types=1);

namespace Smartmage\Inpost\Model\Carrier\Methods\Courier;

use Smartmage\Inpost\Model\Carrier\Methods\AbstractMethod;

class LocalSuperExpress extends AbstractMethod
{
    public $methodKey = 'localsuperexpress';

    public $carrierCode = 'inpostcourier';

    protected $blockAttribute = 'block_send_with_courier';
}
