<?php

namespace Extendware\Core\Block\Adminhtml\System;

use Extendware\Core\Api\ExtendwareModuleRepositoryInterface;
use Magento\Backend\Block\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Helper\Js;
use Magento\Store\Model\ScopeInterface;

class Installed extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    protected $configure;

    protected $moduleList;

    protected $extendwareModuleRepository;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var UrlInterface
     */
    private $urlInterface;

    public function __construct(
        Context $context,
        Session $authSession,
        Js $jsHelper,
        \Extendware\Core\Model\Configure $configure,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        ExtendwareModuleRepositoryInterface $extendwareModuleRepository,
        ScopeConfigInterface $scopeConfig,
        UrlInterface $urlInterface,
        array $data = []
    ) {
        parent::__construct($context, $authSession, $jsHelper, $data);
        $this->configure = $configure;
        $this->moduleList = $moduleList;
        $this->extendwareModuleRepository = $extendwareModuleRepository;
        $this->scopeConfig = $scopeConfig;
        $this->urlInterface = $urlInterface;
    }

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = $this->_getHeaderHtml($element);
        $modules = $this->configure->getAllModule();
        //sort($modules);
        foreach ($modules->getItems() as $module) {
            $html .= $this->getModuleHtml($module);
        }

        $html .= $this->_getFooterHtml($element);

        return $html;
    }

    private function getModuleHtml(\Extendware\Core\Api\Data\ExtendwareModuleInterface $module)
    {
        $baseUrl = $this->scopeConfig->getValue('extendware/general/api_url', ScopeInterface::SCOPE_STORE);
        $domain = $this->scopeConfig->getValue('web/secure/base_url', ScopeInterface::SCOPE_STORE);

        $hash = $module->getLicenseKey();
        $warn = "Incorrect license!";
        $response = false;
        if (!empty($hash)) {
            $warn = "";
            $response = $this->configure->checkModuleStatus($baseUrl, $module, $domain);
//            $this->setConfigValue('extendware_/installed/'.$module['name'], 'no license');
            if (!$response) {
                $warn = "Incorrect license!";
//                $module->setModuleActive(0);
//                $this->extendwareModuleRepository->save($module);
            }
        }

        $html = '<tr>' .
        '<td class="label">' . '
        <label for="extendware_core_installed_' . strtolower($module->getModuleName()) . '">' . '
        <span data-config-scope="[DEFAULT]">' . $module->getModuleName() . '</span>' . '
        </label>' . '
        </td>';
        if (!$response) {
            $html .= '<td class="value" style="vertical-align:bottom;">
							<span class="reginfo">
								' . ($warn ? '<b style="color:#aa0000;">' . $warn . '</b><br />' : '') . '
								<span style="color:red">Not Registered</span>
								<a href="javascript://" onclick="jQuery(this.parentNode).hide(); jQuery(this).closest(\'td\').find(\'.regnow\').show();">(Register Now?)</a>
							</span>
							<span class="regnow" style="display:none">
			                    <span>Enter Your License Key:</span>
								<input type="text" style="margin-top:5px;" name="groups[installed][fields][' .$module->getModuleId() . '][value]" />
								<input type="button" style="margin-top:5px;" onclick="this.disabled=true; jQuery(\'.page-actions #save\').click()"  value="Register"/>
							</span>
						</td>';
        } else {
            $html .= '<td class="value" style="vertical-align:bottom;">
				' . ($warn ? '<b style="color:#00aa00;">' . $warn . '</b><br />' : '') . '
				<span style="color:blue">Registered</span>
			</td>';
        }
        $html .= '<td></td></tr>';
        return $html;
    }
}
