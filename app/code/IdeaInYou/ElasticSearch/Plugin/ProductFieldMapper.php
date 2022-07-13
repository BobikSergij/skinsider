<?php


namespace IdeaInYou\ElasticSearch\Plugin;


class ProductFieldMapper
{

    /**
     * @param \Magento\Elasticsearch\Elasticsearch5\Model\Adapter\FieldMapper\ProductFieldMapper $subject
     * @param string $attributeCode
     * @param array $context
     * @return array
     */
    public function beforeGetFieldName(\Magento\Elasticsearch\Elasticsearch5\Model\Adapter\FieldMapper\ProductFieldMapper $subject, $attributeCode, $context = [])
    {
        $attributeCode = strval($attributeCode);
        return [$attributeCode, $context];
    }
}
