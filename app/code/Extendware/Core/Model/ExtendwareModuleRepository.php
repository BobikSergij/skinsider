<?php

namespace Extendware\Core\Model;

use Extendware\Core\Api\Data\ExtendwareModuleInterface;
use Extendware\Core\Api\Data\ExtendwareModuleInterfaceFactory;
use Extendware\Core\Api\Data\ExtendwareModuleResultsInterfaceFactory;
use Extendware\Core\Model\ResourceModel\ExtendwareModule as ResourceModel;
use Extendware\Core\Model\ResourceModel\ExtendwareModule\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class ExtendwareModuleRepository
 * @package Extendware\Core\Model
 */
class ExtendwareModuleRepository implements \Extendware\Core\Api\ExtendwareModuleRepositoryInterface
{
    /**
     * Model data storage
     *
     * @var array
     */
    private $extendwareModel;

    /**
     * @var ExtendwareModuleInterfaceFactory
     */
    protected $extendwareModelFactory;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var ResourceModel
     */
    protected $resourceModel;

    /**
     * @var ExtendwareModuleResultsInterfaceFactory
     */
    protected $extendwareResultsFactory;

    /**
     * @var mixed
     */
    protected $collectionProcessor;

    /**
     * ExtendwareModuleRepository constructor.
     * @param ResourceModel $resourceModel
     * @param ExtendwareModuleInterfaceFactory $extendwareModelFactory
     * @param CollectionFactory $collectionFactory
     * @param ExtendwareModuleResultsInterfaceFactory $extendwareResultsFactory
     * @param CollectionProcessorInterface|null $collectionProcessor
     */
    public function __construct(
        ResourceModel $resourceModel,
        ExtendwareModuleInterfaceFactory $extendwareModelFactory,
        CollectionFactory $collectionFactory,
        ExtendwareModuleResultsInterfaceFactory $extendwareResultsFactory,
        CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->resourceModel = $resourceModel;
        $this->extendwareModelFactory = $extendwareModelFactory;
        $this->collectionFactory = $collectionFactory;
        $this->extendwareResultsFactory = $extendwareResultsFactory;
        $this->collectionProcessor = $collectionProcessor ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface::class
        );
    }

    /**
     * @inheritDoc
     */
    public function save(ExtendwareModuleInterface $extendwareModule)
    {
        try {
            if ($extendwareModule->getModuleId()) {
                $extendwareModule= $this->getById($extendwareModule->getModuleId())
                ->addData($extendwareModule->getData());
            }
            $this->resourceModel->save($extendwareModule);
            unset($this->extendwareModel[$extendwareModule->getModuleId()]);
        } catch (\Exception $e) {
            if ($extendwareModule->getModuleId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save Module with %1. error %2',
                        [$extendwareModule->getModuleId(),$e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new module. Error: %1', $e->getMessage()));
        }
    }

    /**
     * @inheritDoc
     */
    public function getById($id)
    {
        if (!isset($this->extendwareModel[$id])) {
            $extendwareModel=$this->extendwareModelFactory->create();
            $this->resourceModel->load($extendwareModel, $id);
            if (!$extendwareModel->getModuleId()) {
                throw new NoSuchEntityException(__('Module with specified ID "%1" not found.', $id));
            }
            $this->extendwareModel[$id]=$extendwareModel;
        }
        return $this->extendwareModel[$id];
    }

    /**
     * @inheritDoc
     */
    public function delete(ExtendwareModuleInterface $extendwareModule)
    {
        try {
            $this->resourceModel->delete($extendwareModule);
            unset($this->extendwareModel[$extendwareModule->getModuleId()]);
        } catch (\Exception $e) {
            if ($extendwareModule->getModuleId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove configuration with ID %1. Error: %2',
                        [$extendwareModule->getModuleId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove module. Error: %1', $e->getMessage()));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($id)
    {
        $extendwareModel=$this->getById($id);
        $this->delete($extendwareModel);
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection=$this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResult=$this->extendwareResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());
        return $searchResult;
    }
}
