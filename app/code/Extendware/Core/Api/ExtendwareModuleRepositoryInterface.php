<?php

namespace Extendware\Core\Api;

use Extendware\Core\Api\Data\ExtendwareModuleInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Extendware\Core\Api\Data\ExtendwareModuleResultsInterface;

interface ExtendwareModuleRepositoryInterface
{
    /**
     * save
     *
     * @param ExtendwareModuleInterface $extendwareModule
     *
     * @return ExtendwareModuleInterface
     * @throws CouldNotSaveException
     */
    public function save(ExtendwareModuleInterface $extendwareModule);

    /**
     *  getById
     *
     * @param int $id
     *
     * @return ExtendwareModuleInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);

    /**
     * delete
     *
     * @param ExtendwareModuleInterface $extendwareModule
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(ExtendwareModuleInterface $extendwareModule);

    /**
     * deleteById
     *
     * @param int $id
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($id);

    /**
     *  getList
     *
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return ExtendwareModuleResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);
}
