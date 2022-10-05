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


namespace Mirasvit\Feed\Setup\Patch\Data;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Mirasvit\Feed\Model\Config;
use Mirasvit\Feed\Model\TemplateFactory;
use Mirasvit\Feed\Repository\RuleRepository;
use Mirasvit\Feed\Service\ImportService;

class SetupData implements DataPatchInterface, PatchVersionInterface
{
    private $setup;

    private $templateFactory;

    private $importService;

    private $ruleRepository;

    private $config;

    public function __construct(
        ModuleDataSetupInterface $setup,
        TemplateFactory $templateFactory,
        ImportService $importService,
        RuleRepository $ruleRepository,
        Config $config
    ) {
        $this->setup = $setup;
        $this->templateFactory = $templateFactory;
        $this->importService = $importService;
        $this->ruleRepository    = $ruleRepository;
        $this->config    = $config;
    }


    /**
     * {@inheritdoc}
     */
    public static function getDependencies() {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases() {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion(): string
    {
        return '1.0.1';
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
//        echo "APPLY \n";
        $this->setup->getConnection()->startSetup();
        $dataPath = dirname(dirname(dirname(__FILE__)));
        $templatesPath = $dataPath . '/data/template/';

        foreach (scandir($templatesPath) as $file) {
            if (substr($file, 0, 1) == '.') {
                continue;
            }
//            echo "$file \n";
            $this->templateFactory->create()->import('Setup/data/template/' . $file);
        }

        $rulesPath = $dataPath. '/data/rule/';
        foreach (scandir($rulesPath) as $file) {
            if (substr($file, 0, 1) == '.') {
                continue;
            }
//            echo "$file \n";
            $this->importService->import(
                $this->ruleRepository->create(),
                'Setup/data/rule/' . $file
            );
        }
        $this->setup->getConnection()->endSetup();
    }
}
