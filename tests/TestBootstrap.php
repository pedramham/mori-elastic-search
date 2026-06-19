<?php
declare(strict_types=1);

use Shopware\Core\TestBootstrapper;

// Initialize Shopware's TestBootstrapper
$loader = (new TestBootstrapper())
    ->addCallingPlugin()
    ->addActivePlugins('MoriElasticSearch') // Use the technical name of your plugin base class
    ->setForceInstallPlugins(true)
    ->bootstrap()
    ->getClassLoader();

// Explicitly register the plugin's source and test namespaces
$loader->addPsr4('MoriElasticSearch\\', dirname(__DIR__) . '/src/');
$loader->addPsr4('MoriElasticSearch\\Tests\\', __DIR__ . '/');

return $loader;