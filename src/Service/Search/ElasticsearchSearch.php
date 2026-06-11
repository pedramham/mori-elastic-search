<?php

declare(strict_types=1);

namespace MoriElasticSearch\Service\Search;

use OpenSearch\Client;
use OpenSearch\ClientBuilder;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class ElasticsearchSearch
{
    private Client $client;
    private string $indexName;
    private SystemConfigService $systemConfig;

    public function __construct(SystemConfigService $systemConfig)
    {
        $this->systemConfig = $systemConfig;
        $host = $systemConfig->get('MoriElasticSearch.config.elasticSearchHost') ?? 'http://localhost:9200';
        $this->client = ClientBuilder::create()->setHosts([$host])->build();
        $this->indexName = $systemConfig->get('MoriElasticSearch.config.elasticSearchIndexName',) ?? 'sw_pdf_documents_v1';
    }

    public function search(string $term): array
    {
        $words = explode(' ', trim($term));
        $wordCount = count($words);

        if ($wordCount >= 3) {
            $results = $this->executeSpanNearSearch($words);
            if (empty($results)) {
                $results = $this->executeMultiMatchSearch($term);
            }
        } else {
            $results = $this->executeMultiMatchSearch($term);
        }

        return $results;
    }

    private function executeSpanNearSearch(array $words): array
    {
        $clauses = array_map(function ($word) {
            return ['span_term' => ['description' => ['value' => $word]]];
        }, $words);

        $slop = $this->systemConfig->get('MoriElasticSearch.config.elasticSearchSlop');

        $params = [
            'index' => $this->indexName,
            'body' => [
                'query' => [
                    'span_near' => [
                        'clauses' => $clauses,
                        'slop' => $slop,
                        'in_order' => true
                    ]
                ],
                'collapse' => ['field' => 'mediaId'],
                'size' => 1
            ]
        ];

        $response = $this->client->search($params);
        return $response['hits']['hits'];
    }

    private function executeMultiMatchSearch(string $term): array
    {
        $fuzziness = $this->systemConfig->get('MoriElasticSearch.config.elasticSearchFuzziness');
        $params = [
            'index' => $this->indexName,
            'body' => [
                'query' => [
                    'multi_match' => [
                        'query' => $term,
                        'fields' => ['title^3', 'description'],
                        'fuzziness' => $fuzziness ?? 'AUTO'
                    ]
                ],
                'size' => 10
            ]
        ];

        $response = $this->client->search($params);
        return $response['hits']['hits'];
    }
}