<?php

declare(strict_types=1);

namespace MoriElasticSearch\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1780983980CreatePdfElasticSearchTable extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1780983980;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `pdf_elastic_search` (
    `id` BINARY(16) NOT NULL,
    `media_id` BINARY(16) NOT NULL,
    `title` VARCHAR(255) COLLATE utf8mb4_unicode_ci,
    `path` VARCHAR(255) COLLATE utf8mb4_unicode_ci,
    `description` LONGTEXT COLLATE utf8mb4_unicode_ci,
    `active` TINYINT(1) COLLATE utf8mb4_unicode_ci,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3),
    PRIMARY KEY (`id`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;
SQL;

        $connection->executeStatement($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
