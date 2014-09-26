<?php
/**
 *
 * @author Mihkel Viilveer <mihkel@opus.ee>
 * @date 25.08.2014
 */

use \opus\elastic\enum\ElasticFieldType;

return [
    ElasticFieldType::INTEGER => [
        'type' => 'integer',
    ],
    ElasticFieldType::STRING => [
        'type' => 'string',
        'index' => 'not_analyzed'
    ],
    ElasticFieldType::ANALYZED_STRING => [
        'type' => 'string',
        'index' => 'analyzed',
        'analyzer' => 'standard',
    ],
    ElasticFieldType::ANALYZED_STRING_WITH_RAW => [
        'type' => 'string',
        'index' => 'analyzed',
        'analyzer' => 'standard',
        'fields' => [
            'raw' => [
                'type' => 'string',
                'index' => 'not_analyzed'
            ]
        ]
    ],
    ElasticFieldType::DATE => [
        'type' => 'date',
        'format' => 'YYYY-MM-dd HH:mm:ss',
    ],
    ElasticFieldType::FLOAT => [
        'type' => 'float',
    ],
    ElasticFieldType::OBJECT => [
        'type' => 'object',
    ],
    ElasticFieldType::NESTED => [
        'type' => 'nested',
    ],
    ElasticFieldType::BOOLEAN => [
        'type' => 'boolean',
    ],
];