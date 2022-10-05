<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-feed
 * @version   1.2.8
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);


namespace Mirasvit\Feed\Ui\Rule\Form\ProductListing;


use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Mirasvit\Core\Service\SerializeService;
use Mirasvit\Feed\Controller\Adminhtml\Rule\Preview;
use Mirasvit\Feed\Repository\RuleRepository;

class DataProvider extends ProductDataProvider
{
    private $repository;

    private $request;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        RuleRepository $repository,
        RequestInterface $request,
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $collectionFactory,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = [],
        PoolInterface $modifiersPool = null
    ){
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $collectionFactory,
            $addFieldStrategies,
            $addFilterStrategies,
            $meta,
            $data,
            $modifiersPool
        );

        $this->request    = $request;
        $this->repository = $repository;

        $ruleId = (int)$this->request->getParam('rule_id');

        if (!$ruleId) {
            $rule = $this->repository->create();
        } else {
            $rule = $this->repository->get($ruleId);
        }

        if ($conditions = $this->request->getParam('rule')) {
            $ruleInstance = $this->repository->getRuleInstance($rule);

            $conditions = $ruleInstance->loadPost($conditions)
                ->getConditions()->asArray();

            $rule->setConditionsSerialized(SerializeService::encode($conditions));
        }

        $ruleInstance = $this->repository->getRuleInstance($rule);
        $sqlCondition = $ruleInstance->getConditions()->getSqlCondition($this->collection);

        if ($sqlCondition) {
            $this->collection->getSelect()->where($sqlCondition)->group('entity_id');
        }
    }
}
