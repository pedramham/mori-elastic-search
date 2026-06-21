<?php

declare(strict_types=1);

namespace MoriElasticSearch\Service\Search;

use MoriElasticSearch\Config\SystemConfigHelper;
use OpenSearch\Client;
use OpenSearch\ClientBuilder;

class ElasticsearchSearch
{
    private Client $client;

    public function __construct(?Client $client = null)
    {
        $this->client = $client ?? ClientBuilder::create()->setHosts([SystemConfigHelper::getHost()])->build();
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
            return [
                'span_term' => [
                    'description' => [
                        'value' => $word,
                    ],
                ],
            ];
        }, $words);

        $params = [
            'index' => SystemConfigHelper::getIndexName(),
            'body' => [
                'query' => [
                    'span_near' => [
                        'clauses' => $clauses,
                        'slop' => SystemConfigHelper::getSlop(),
                        'in_order' => true,
                    ],
                ],
                'highlight' => [
                    'fields' => [
                        'description' => [
                            'fragment_size' => 200,
                            'number_of_fragments' => 1,
                            'no_match_size' => 200,
                            'pre_tags' => [' <span class="highlight-description">'],
                            'post_tags' => ['</span> '],
                        ],
                    ],
                ],
                '_source' => ['title', 'converted_at', 'url'],
                'collapse' => [
                    'field' => 'mediaId',
                ],
                'size' => 1,
            ],
        ];

        $response = $this->client->search($params);
        return $response['hits']['hits'];
    }

    private function executeMultiMatchSearch(string $term): array
    {
        $params = [
            'index' => SystemConfigHelper::getIndexName(),
            'body' => [
                'query' => [
                    'multi_match' => [
                        'query' => $term,
                        'fields' => ['title^3', 'description'],
                        'fuzziness' => SystemConfigHelper::getFuzziness(),
                    ],
                ],
                'highlight' => [
                    'fields' => [
                        'description' => [
                            'fragment_size' => 200,
                            'number_of_fragments' => 1,
                            'no_match_size' => 200,
                            'pre_tags' => [' <span class="highlight-description">'],
                            'post_tags' => ['</span> '],
                        ],
                    ],
                ],
                '_source' => ['title', 'converted_at', 'url'],
                'size' => 10,
            ],
        ];

        $response = $this->client->search($params);
        return $response['hits']['hits'];
    }
}
