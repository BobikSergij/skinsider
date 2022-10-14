<?php

namespace Extendware\Core\Model\Message;

use Extendware\Core\Api\Data\ExtendwareModuleInterface;
use Extendware\Core\Api\ExtendwareModuleRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use \Magento\Backend\Model\Url as UrlInterface;
use Ramsey\Uuid\Uuid;

/**
 * Class Notification
 * @package Extendware\Core\Model\Message
 */
class Notification implements \Magento\Framework\Notification\MessageInterface
{

    /**
     * @var ExtendwareModuleRepositoryInterface
     */
    protected $extendwareModuleRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Ramsey\Uuid\UuidFactoryInterface
     */
    private $uuidFactory;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var array
     */
    private $disabledModuleList = [];

    /**
     * Notification constructor.
     * @param ExtendwareModuleRepositoryInterface $extendwareModuleRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        ExtendwareModuleRepositoryInterface $extendwareModuleRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        UrlInterface $urlBuilder
    ) {
        $this->extendwareModuleRepository = $extendwareModuleRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->urlBuilder = $urlBuilder;

        /**
         * Temp solution because Magento 2.1 DI breaks on classes with type-hints
         * @codingStandardsIgnoreLine
         */
        $this->uuidFactory = new \Ramsey\Uuid\UuidFactory();
    }

    /**
     * @inheritDoc
     */
    public function getIdentity()
    {
        return $this->generateIdForData('EXTENDWARE_CONFIG_NOTIFICATION');
    }

    /**
     * Borrowed function from Magento 2.2. See \Magento\Framework\DataObject\IdentityService
     *
     * @param $data
     * @return string
     */
    private function generateIdForData($data)
    {
        // Cannot use \Magento\Framework\DataObject\IdentityGeneratorInterface because
        // it doesn't exist in Magento 2.1
        $uuid = $this->uuidFactory->uuid3(Uuid::NAMESPACE_DNS, $data);
        return $uuid->toString();
    }

    /**
     * @return bool|\Extendware\Core\Api\Data\ExtendwareModuleResultsInterface
     */
    public function getDisabledModule()
    {
        try {
            $searchCriteria = $this->searchCriteriaBuilder->addFilter(
                ExtendwareModuleInterface::MODULE_ACTIVE,
                0,
                'eq'
            )->create();
            return $this->extendwareModuleRepository->getList($searchCriteria);
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function isDisplayed()
    {
        if ($disableModule = $this->getDisabledModule()) {
            $this->disabledModuleList = $disableModule->getItems();
            return $disableModule->getTotalCount() > 0;
        }

        return false;
    }

    /**
     * @return string
     */
    private function getDisabledModuleName()
    {
        $name = '';
        /** @var \Extendware\Core\Api\Data\ExtendwareModuleInterface $module */
        foreach ($this->disabledModuleList as $module) {
            $name .= $module->getModuleName() . ', ';
        }
        return rtrim($name, ', ');
    }

    /**
     * @inheritDoc
     */
    public function getText()
    {
        $messageDetails = '';

        $messageDetails .= '<strong>';
        $messageDetails .= __('Warning modules are not registered.');
        $messageDetails .= '</strong><p>';
        $messageDetails .= __('Module(s) affected: ');
        $messageDetails .= $this->getDisabledModuleName();
        $messageDetails .= '</p><p>';
        $messageDetails .= __(
            'Click here to go to <a href="%1">Extendware Configuration</a> and add your license.',
            $this->getManageGeneralUrl()
        );
        $messageDetails .= "</p>";

        // Message text
        return __($messageDetails);
    }

    /**
     * @return string
     */
    public function getManageGeneralUrl()
    {
        return $this->urlBuilder->getUrl('adminhtml/system_config/edit', ['section' => 'extendware_core']);
    }

    /**
     * @inheritDoc
     */
    public function getSeverity()
    {
        return self::SEVERITY_CRITICAL;
    }
}
