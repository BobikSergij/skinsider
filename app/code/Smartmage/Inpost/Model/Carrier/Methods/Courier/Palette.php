<?php

declare(strict_types=1);

namespace Smartmage\Inpost\Model\Carrier\Methods\Courier;

use Smartmage\Inpost\Model\Carrier\Methods\AbstractMethod;

class Palette extends AbstractMethod
{
    public $methodKey = 'palette';

    public $carrierCode = 'inpostcourier';

    protected $blockAttribute = 'block_send_with_palette';
}
