<?php

declare(strict_types=1);

namespace Smartmage\Inpost\Model\Carrier\Methods\Courier;

use Smartmage\Inpost\Model\Carrier\Methods\AbstractMethod;

class LocalExpress extends AbstractMethod
{
    public $methodKey = 'localexpress';

    public $carrierCode = 'inpostcourier';

    protected $blockAttribute = 'block_send_with_courier';
}
