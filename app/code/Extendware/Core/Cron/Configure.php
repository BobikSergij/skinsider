<?php

namespace Extendware\Core\Cron;

class Configure
{
    public function __construct(
        \Extendware\Core\Model\Configure $configure
    ) {
        $this->configure = $configure;
    }


    public function execute()
    {
        $this->configure->execute();
    }
}
