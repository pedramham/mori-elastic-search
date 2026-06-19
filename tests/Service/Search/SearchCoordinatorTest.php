<?php

declare(strict_types=1);

namespace MoriElasticSearch\Tests\Service\Search;

use MoriElasticSearch\Service\Search\DatabaseSearch;
use MoriElasticSearch\Service\Search\ElasticsearchSearch;
use MoriElasticSearch\Service\Search\SearchCoordinator;
use MoriElasticSearch\Struct\PdfSearchResultStruct;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;

#[CoversClass(SearchCoordinator::class)]
class SearchCoordinatorTest extends TestCase
{
    private ElasticsearchSearch $elasticsearchSearchMock;
    private DatabaseSearch $databaseSearchMock;
    private SearchCoordinator $searchCoordinator;
    private Context $contextStub; // Renamed to represent a Stub

    protected function setUp(): void
    {
        parent::setUp();

        $this->elasticsearchSearchMock = $this->createMock(ElasticsearchSearch::class);
        $this->databaseSearchMock = $this->createMock(DatabaseSearch::class);

        $this->contextStub = $this->createStub(Context::class);
        $this->searchCoordinator = new SearchCoordinator(
            $this->elasticsearchSearchMock,
            $this->databaseSearchMock
        );
    }

    public function testSearchReturnsElasticsearchResultsWhenNotEmpty(): void
    {
        $term = 'matching-term';
        $esResults = [
            ['id' => '1', 'title' => 'PDF Doc A'],
            ['id' => '2', 'title' => 'PDF Doc B']
        ];

        // Expect Elasticsearch search to run once and return results
        $this->elasticsearchSearchMock->expects($this->once())
            ->method('search')
            ->with($term)
            ->willReturn($esResults);

        // Expect Database search to never run since Elasticsearch was successful
        $this->databaseSearchMock->expects($this->never())
            ->method('search');

        $result = $this->searchCoordinator->search($term, $this->contextStub);

        // Assert structural response state
        $this->assertInstanceOf(PdfSearchResultStruct::class, $result);
        $this->assertSame($esResults, $result->getResults());
        $this->assertSame(2, $result->getTotal());
    }

    public function testSearchFallsBackToDatabaseSearchWhenElasticsearchIsEmpty(): void
    {
        $term = 'fallback-term';
        $esResults = []; // No results in ES
        $dbResults = [
            ['id' => '3', 'title' => 'Database Backup Doc']
        ];

        // Expect Elasticsearch search to run once and return nothing
        $this->elasticsearchSearchMock->expects($this->once())
            ->method('search')
            ->with($term)
            ->willReturn($esResults);

        // Expect Database search to be queried as a fallback
        $this->databaseSearchMock->expects($this->once())
            ->method('search')
            ->with($term, $this->contextStub)
            ->willReturn($dbResults);

        $result = $this->searchCoordinator->search($term, $this->contextStub);

        // Assert structural response state contains database results
        $this->assertInstanceOf(PdfSearchResultStruct::class, $result);
        $this->assertSame($dbResults, $result->getResults());
        $this->assertSame(1, $result->getTotal());
    }
}