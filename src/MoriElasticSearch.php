<?php

declare(strict_types=1);

namespace MoriElasticSearch;

use MoriElasticSearch\Config\SystemConfigHelper;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class MoriElasticSearch extends Plugin
{
    public function uninstall(UninstallContext $uninstallContext): void
    {
        parent::uninstall($uninstallContext);

        if ($uninstallContext->keepUserData()) {
            return;
        }
    }

    public function boot(): void
    {
        parent::boot();

        /** @var SystemConfigService $systemConfig */
        $systemConfig = $this->container->get(SystemConfigService::class);
        SystemConfigHelper::initialize($systemConfig);
    }
}
