<?php
/**
 *
 * @author Mihkel Viilveer <mihkel@opus.ee>
 * @date 9.09.2014
 */

namespace opus\elastic\spooler;

use opus\elastic\enum\ElasticFieldType;

/**
 * Interface AbstractMappingProvider
 *
 * @package opus\elastic\spooler
 */
abstract class AbstractMappingProvider
{
    /**
     * Creates mapping and returns it
     * @param array $languages
     * @return array
     */
    abstract public function build(array $languages);

    /**
     * Processes mapping attributes data
     * Formats them to correct format
     * @param array $productAttributesData
     *
     * @return array
     */
    public function processMappingAttributesData(array $productAttributesData)
    {
        $formattedAttributes = [];
        foreach ($productAttributesData as $name => $options) {
            $options = !is_array($options) ? [$options] : $options;
            $formattedAttributes[$name] = $this->getAttributeProperties($options);
        }
        return $formattedAttributes;
    }

    /**
     * Gets attribute properties
     * Recursive if index type is object or nested
     *
     * @param array $options
     *
     * @return mixed
     */
    private function getAttributeProperties(array $options)
    {
        $attributeProperties = $this->resolveProperties($options[0]);
        if (in_array($options[0], [ElasticFieldType::OBJECT, ElasticFieldType::NESTED])) {
            $attributeProperties['properties'] = $this->processMappingAttributesData($options['properties']);
        }
        return $attributeProperties;
    }

    /**
     * @param $type
     */
    public function resolveProperties($type)
    {
        /**
         * @var $resolver TypeResolver
         */
        $resolver = \Yii::createObject('opus\elastic\spooler\TypeResolver');
        return $resolver->resolve($type);
    }
} 