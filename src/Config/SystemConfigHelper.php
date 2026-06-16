<?php

namespace MoriElasticSearch\Config;

use Shopware\Core\System\SystemConfig\SystemConfigService;

class SystemConfigHelper
{
    private static SystemConfigService $systemConfig;

    public static function initialize(SystemConfigService $systemConfig): void
    {
        self::$systemConfig = $systemConfig;
    }

    public static function getHost(): string
    {
        return self::getStringConfig(
            'MoriElasticSearch.config.elasticSearchHost',
            'http://localhost:9200'
        );
    }

    public static function getIndexName(): string
    {
        return self::getStringConfig(
            'MoriElasticSearch.config.elasticSearchIndexName',
            'sw_pdf_documents_v1'
        );
    }

    public static function getNumberOfShards(): int
    {
        return self::getIntConfig(
            'MoriElasticSearch.config.elasticSearchNumberOfShards',
            1
        );
    }

    public static function getNumberOfReplicas(): int
    {
        return self::getIntConfig(
            'MoriElasticSearch.config.elasticSearchNumberOfReplicas',
            0
        );
    }

    public static function getFuzziness(): string
    {
        return self::getStringConfig(
            'MoriElasticSearch.config.elasticSearchFuzziness',
            'AUTO'
        );
    }

    public static function getSlop(): int
    {
        return self::getIntConfig(
            'MoriElasticSearch.config.elasticSearchSlop',
            0
        );
    }

    private static function getStringConfig(string $key, string $default): string
    {
        $value = self::$systemConfig->get($key);
        return is_string($value) ? $value : $default;
    }

    private static function getIntConfig(string $key, int $default): int
    {
        $value = self::$systemConfig->get($key);
        return is_int($value) ? $value : $default;
    }
}
