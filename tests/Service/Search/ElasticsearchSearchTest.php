<?php

declare(strict_types=1);

namespace MoriElasticSearch\Tests\Service\Search;

use MoriElasticSearch\Service\Search\ElasticsearchSearch;
use OpenSearch\Client;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(ElasticsearchSearch::class)]
class ElasticsearchSearchTest extends TestCase
{
    public function testSearchWithShortTermExecutesMultiMatchQueryDirectly(): void
    {
        $term = 'short query'; // 2 words
        $expectedHits = [['_id' => 'doc-1', '_source' => ['title' => 'Short Result']]];

        $clientMock = $this->createMock(Client::class);
        $clientMock->expects($this->once())
            ->method('search')
            ->with($this->callback(function (array $params) use ($term) {
                // Assert that the payload contains a multi_match query
                $this->assertArrayHasKey('query', $params['body']);
                $this->assertArrayHasKey('multi_match', $params['body']['query']);
                $this->assertSame($term, $params['body']['query']['multi_match']['query']);
                return true;
            }))
            ->willReturn(['hits' => ['hits' => $expectedHits]]);

        $searchService = new ElasticsearchSearch($clientMock);
        $results = $searchService->search($term);

        $this->assertSame($expectedHits, $results);
    }

    public function testSearchWithLongTermReturnsSpanNearResultsEarly(): void
    {
        $term = 'three word query'; // 3 words
        $expectedHits = [['_id' => 'doc-2', '_source' => ['title' => 'SpanNear Result']]];

        $clientMock = $this->createMock(Client::class);
        $clientMock->expects($this->once())
            ->method('search')
            ->with($this->callback(function (array $params) {
                // Assert that the payload contains a span_near query
                $this->assertArrayHasKey('query', $params['body']);
                $this->assertArrayHasKey('span_near', $params['body']['query']);
                return true;
            }))
            ->willReturn(['hits' => ['hits' => $expectedHits]]);

        $searchService = new ElasticsearchSearch($clientMock);
        $results = $searchService->search($term);

        $this->assertSame($expectedHits, $results);
    }

    public function testSearchWithLongTermFallsBackToMultiMatchWhenSpanNearIsEmpty(): void
    {
        $term = 'three word query';
        $fallbackHits = [['_id' => 'doc-3', '_source' => ['title' => 'MultiMatch Fallback']]];

        $clientMock = $this->createMock(Client::class);

        $clientMock->expects($this->exactly(2))
            ->method('search')
            ->willReturnOnConsecutiveCalls(
                ['hits' => ['hits' => []]],
                ['hits' => ['hits' => $fallbackHits]]
            );

        $searchService = new ElasticsearchSearch($clientMock);
        $results = $searchService->search($term);

        $this->assertSame($fallbackHits, $results);
    }
}