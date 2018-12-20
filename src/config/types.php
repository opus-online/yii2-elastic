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
        'type' => 'keyword',
    ],
    ElasticFieldType::ANALYZED_STRING => [
        'type' => 'text',
    ],
    ElasticFieldType::ANALYZED_STRING_WITH_RAW => [
        'type' => 'text',
        'fields' => [
            'raw' => [
                'type' => 'keyword',
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