<?php

namespace IdeaInYou\Alsoviewed\Model\ResourceModel\Relation;

class Collection extends \IdeaInYou\Alsoviewed\Model\ResourceModel\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'relation_id';

    protected function _construct()
    {
        $this->_init(
            'IdeaInYou\Alsoviewed\Model\Relation',
            'IdeaInYou\Alsoviewed\Model\ResourceModel\Relation'
        );
    }
}
