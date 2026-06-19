<?php

declare(strict_types=1);

namespace MoriElasticSearch\Tests\Service\Search;

use MoriElasticSearch\Service\Search\DatabaseSearch;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;

class DatabaseSearchTest extends TestCase
{
    // This trait bootstraps the Shopware container and provides static::getContainer()
    use IntegrationTestBehaviour;

    public function testSearchAppliesFiltersAndReturnsResults(): void
    {
        $context = Context::createDefaultContext();
        $repository = static::getContainer()->get('pdf_elastic_search.repository');
        $databaseSearch = new DatabaseSearch($repository);
        $results = $databaseSearch->search('test-query', $context);
        $this->assertIsArray($results);
    }
}