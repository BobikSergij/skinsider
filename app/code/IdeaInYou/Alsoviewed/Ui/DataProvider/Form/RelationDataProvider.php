<?php

namespace IdeaInYou\Alsoviewed\Ui\DataProvider\Form;

class RelationDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \IdeaInYou\Alsoviewed\Model\Relation\Locator\LocatorInterface
     */
    protected $locator;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param \IdeaInYou\Alsoviewed\Model\ResourceModel\Relation\Collection $collection
     * @param \IdeaInYou\Alsoviewed\Model\Relation\Locator\LocatorInterface $locator
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \IdeaInYou\Alsoviewed\Model\ResourceModel\Relation\Collection $collection,
        \IdeaInYou\Alsoviewed\Model\Relation\Locator\LocatorInterface $locator,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collection;
        $this->locator = $locator;
    }

    public function getData()
    {
        $relation = $this->locator->getRelation();
        return [
            $relation->getId() => $relation->getData()
        ];
    }
}
