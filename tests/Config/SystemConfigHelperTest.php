<?php

declare(strict_types=1);

namespace MoriElasticSearch\Tests\Config;

use MoriElasticSearch\Config\SystemConfigHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\Core\System\SystemConfig\SystemConfigService;

#[CoversClass(SystemConfigHelper::class)]
class SystemConfigHelperTest extends TestCase
{
    /**
     * TEST 1: Verifies getHost() returns the configured host string when present.
     */
    public function testGetHostReturnsConfiguredValue(): void
    {
        $systemConfigMock = $this->createMock(SystemConfigService::class);
        $systemConfigMock->expects($this->once())
            ->method('get')
            ->with('MoriElasticSearch.config.elasticSearchHost')
            ->willReturn('http://custom-host:9200');

        SystemConfigHelper::initialize($systemConfigMock);

        $this->assertSame('http://custom-host:9200', SystemConfigHelper::getHost());
    }

    /**
     * TEST 2: Verifies getHost() falls back to default when configuration is missing.
     */
    public function testGetHostFallsBackToDefaultWhenConfigIsMissing(): void
    {
        $systemConfigMock = $this->createMock(SystemConfigService::class);
        $systemConfigMock->expects($this->once())
            ->method('get')
            ->with('MoriElasticSearch.config.elasticSearchHost')
            ->willReturn(null);

        SystemConfigHelper::initialize($systemConfigMock);

        $this->assertSame('http://localhost:9200', SystemConfigHelper::getHost());
    }

    /**
     * TEST 3: Verifies getIndexName() returns the configured index name.
     */
    public function testGetIndexNameReturnsConfiguredValue(): void
    {
        $systemConfigMock = $this->createMock(SystemConfigService::class);
        $systemConfigMock->expects($this->once())
            ->method('get')
            ->with('MoriElasticSearch.config.elasticSearchIndexName')
            ->willReturn('custom_index_v1');

        SystemConfigHelper::initialize($systemConfigMock);

        $this->assertSame('custom_index_v1', SystemConfigHelper::getIndexName());
    }

    /**
     * TEST 4: Verifies getIndexName() falls back to default when configuration is missing.
     */
    public function testGetIndexNameFallsBackToDefaultWhenConfigIsMissing(): void
    {
        $systemConfigMock = $this->createMock(SystemConfigService::class);
        $systemConfigMock->expects($this->once())
            ->method('get')
            ->with('MoriElasticSearch.config.elasticSearchIndexName')
            ->willReturn(null);

        SystemConfigHelper::initialize($systemConfigMock);

        $this->assertSame('sw_pdf_documents_v1', SystemConfigHelper::getIndexName());
    }

    /**
     * TEST 5: Verifies getNumberOfShards() returns the configured integer.
     */
    public function testGetNumberOfShardsReturnsConfiguredValue(): void
    {
        $systemConfigMock = $this->createMock(SystemConfigService::class);
        $systemConfigMock->expects($this->once())
            ->method('get')
            ->with('MoriElasticSearch.config.elasticSearchNumberOfShards')
            ->willReturn(3);

        SystemConfigHelper::initialize($systemConfigMock);

        $this->assertSame(3, SystemConfigHelper::getNumberOfShards());
    }

    /**
     * TEST 6: Verifies getNumberOfShards() falls back to default when configuration is of wrong type.
     */
    public function testGetNumberOfShardsFallsBackToDefaultWhenConfigIsInvalidType(): void
    {
        $systemConfigMock = $this->createMock(SystemConfigService::class);
        $systemConfigMock->expects($this->once())
            ->method('get')
            ->with('MoriElasticSearch.config.elasticSearchNumberOfShards')
            ->willReturn('not-an-integer'); // Invalid type fallback check

        SystemConfigHelper::initialize($systemConfigMock);

        $this->assertSame(1, SystemConfigHelper::getNumberOfShards());
    }

    /**
     * TEST 7: Verifies getNumberOfReplicas() returns the configured integer.
     */
    public function testGetNumberOfReplicasReturnsConfiguredValue(): void
    {
        $systemConfigMock = $this->createMock(SystemConfigService::class);
        $systemConfigMock->expects($this->once())
            ->method('get')
            ->with('MoriElasticSearch.config.elasticSearchNumberOfReplicas')
            ->willReturn(2);

        SystemConfigHelper::initialize($systemConfigMock);

        $this->assertSame(2, SystemConfigHelper::getNumberOfReplicas());
    }

    /**
     * TEST 8: Verifies getNumberOfReplicas() falls back to default when configuration is missing.
     */
    public function testGetNumberOfReplicasFallsBackToDefaultWhenConfigIsMissing(): void
    {
        $systemConfigMock = $this->createMock(SystemConfigService::class);
        $systemConfigMock->expects($this->once())
            ->method('get')
            ->with('MoriElasticSearch.config.elasticSearchNumberOfReplicas')
            ->willReturn(null);

        SystemConfigHelper::initialize($systemConfigMock);

        $this->assertSame(0, SystemConfigHelper::getNumberOfReplicas());
    }

    /**
     * TEST 9: Verifies getFuzziness() returns the configured string.
     */
    public function testGetFuzzinessReturnsConfiguredValue(): void
    {
        $systemConfigMock = $this->createMock(SystemConfigService::class);
        $systemConfigMock->expects($this->once())
            ->method('get')
            ->with('MoriElasticSearch.config.elasticSearchFuzziness')
            ->willReturn('1');

        SystemConfigHelper::initialize($systemConfigMock);

        $this->assertSame('1', SystemConfigHelper::getFuzziness());
    }

    /**
     * TEST 10: Verifies getFuzziness() falls back to default when configuration is missing.
     */
    public function testGetFuzzinessFallsBackToDefaultWhenConfigIsMissing(): void
    {
        $systemConfigMock = $this->createMock(SystemConfigService::class);
        $systemConfigMock->expects($this->once())
            ->method('get')
            ->with('MoriElasticSearch.config.elasticSearchFuzziness')
            ->willReturn(null);

        SystemConfigHelper::initialize($systemConfigMock);

        $this->assertSame('AUTO', SystemConfigHelper::getFuzziness());
    }

    /**
     * TEST 11: Verifies getSlop() returns the configured integer.
     */
    public function testGetSlopReturnsConfiguredValue(): void
    {
        $systemConfigMock = $this->createMock(SystemConfigService::class);
        $systemConfigMock->expects($this->once())
            ->method('get')
            ->with('MoriElasticSearch.config.elasticSearchSlop')
            ->willReturn(4);

        SystemConfigHelper::initialize($systemConfigMock);

        $this->assertSame(4, SystemConfigHelper::getSlop());
    }

    /**
     * TEST 12: Verifies getSlop() falls back to default when configuration is missing.
     */
    public function testGetSlopFallsBackToDefaultWhenConfigIsMissing(): void
    {
        $systemConfigMock = $this->createMock(SystemConfigService::class);
        $systemConfigMock->expects($this->once())
            ->method('get')
            ->with('MoriElasticSearch.config.elasticSearchSlop')
            ->willReturn(null);

        SystemConfigHelper::initialize($systemConfigMock);

        $this->assertSame(0, SystemConfigHelper::getSlop());
    }
}