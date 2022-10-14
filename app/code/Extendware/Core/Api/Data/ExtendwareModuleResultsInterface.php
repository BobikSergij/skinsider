<?php

namespace Extendware\Core\Api\Data;

use Magento\Framework\Data\SearchResultInterface;

interface ExtendwareModuleResultsInterface extends SearchResultInterface
{
    /**
     * Get Pages list
     *
     * @return \Extendware\Core\Api\Data\ExtendwareModuleInterface[]
     */
    public function getItems();

    /**
     * Set Pages list
     *
     * @param \Extendware\Core\Api\Data\ExtendwareModuleInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
