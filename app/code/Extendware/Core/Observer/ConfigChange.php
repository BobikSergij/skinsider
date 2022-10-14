<?php

namespace Extendware\Core\Observer;

use Extendware\Core\Api\ExtendwareModuleRepositoryInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class ConfigChange implements ObserverInterface
{
    const XML_PATH_EXTENDWARE_URL = 'extenware_core/general/api_url';

    private $request;

    private $configWriter;

    protected $extendwareModuleRepository;

    public function __construct(
        RequestInterface $request,
        WriterInterface $configWriter,
        ExtendwareModuleRepositoryInterface $extendwareModuleRepository
    ) {
        $this->request = $request;
        $this->configWriter = $configWriter;
        $this->extendwareModuleRepository = $extendwareModuleRepository;
    }

    /**
     * @inheritDoc
     */
    public function execute(EventObserver $observer)
    {
        $configData = $observer->getEvent()->getData('configData');
        if ($configData['section'] != 'extendware_core') {
            return;
        }

        $request = $observer->getEvent()->getData('request');
        if ($groups = $request->getPost('groups')) {
            if (array_key_exists('installed', $groups)) {
                $fields = $groups['installed']['fields'];
                foreach ($fields as $key => $field) {
                    /** @var \Extendware\Core\Api\Data\ExtendwareModuleInterface $module */
                    $module = $this->extendwareModuleRepository->getById($key);
                    $module->setLicenseKey($field['value']);
                    $this->extendwareModuleRepository->save($module);
                }
            }
        }

        return;
    }
}
