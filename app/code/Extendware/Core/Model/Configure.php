<?php

namespace Extendware\Core\Model;

use Extendware\Core\Api\Data\ExtendwareModuleInterface;
use Extendware\Core\Api\ExtendwareModuleRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Module\Status;
use Magento\Store\Model\ScopeInterface;

class Configure
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
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Status
     */
    private $status;

    /**
     * Configure constructor.
     * @param ExtendwareModuleRepositoryInterface $extendwareModuleRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ScopeConfigInterface $scopeConfig
     * @param Status $status
     */
    public function __construct(
        ExtendwareModuleRepositoryInterface $extendwareModuleRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ScopeConfigInterface $scopeConfig,
        Status $status
    )
    {
        $this->extendwareModuleRepository = $extendwareModuleRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->status = $status;
    }

    public function execute()
    {
        $searchResults = $this->getActiveModule();
        $baseUrl = $this->scopeConfig->getValue('extendware/general/api_url', ScopeInterface::SCOPE_STORE);
        $domain = $this->scopeConfig->getValue('web/secure/base_url', ScopeInterface::SCOPE_STORE);

        /** @var ExtendwareModuleInterface $item */
        foreach ($searchResults->getItems() as $item) {
            $this->checkModuleStatus($baseUrl, $item, $domain);
        }
    }

    public function checkModuleStatus(string $baseURL, ExtendwareModuleInterface $extendwareModule, string $domain)
    {
        $url = $baseURL .
            "?license=" . urlencode($extendwareModule->getLicenseKey()) .
            "&domain=" . urlencode($domain) .
            "&moduleName=" . urlencode($extendwareModule->getModuleName());
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
        ]);

        $response = curl_exec($curl);
        curl_close($curl);
        $response = $response == 'true' ? true : false;

        $this->status->setIsEnabled($response, [$extendwareModule->getModuleName()]);
        $extendwareModule->setModuleActive($response);
        $this->extendwareModuleRepository->save($extendwareModule);
        return $response;
    }

    public function getActiveModule()
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter(
            ExtendwareModuleInterface::MODULE_ACTIVE,
            1,
            'eq'
        )->create();
        return $this->extendwareModuleRepository->getList($searchCriteria);
    }

    public function getAllModule()
    {
        $searchCriteria = $this->searchCriteriaBuilder->create();
        return $this->extendwareModuleRepository->getList($searchCriteria);
    }
}
