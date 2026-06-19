<?php

declare(strict_types=1);

namespace MoriElasticSearch\Service;

use MoriElasticSearch\Config\SystemConfigHelper;
use OpenSearch\Client;
use OpenSearch\ClientBuilder;
use Psr\Log\LoggerInterface;

class ElasticsearchStorage implements ConvertPdfInterface
{
    private Client $client;

    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger, ?Client $client = null)
    {
        $this->client = $client ?? ClientBuilder::create()->setHosts([SystemConfigHelper::getHost()])->build();
        $this->logger = $logger;
    }

    public function save(array $data): void
    {
        if (! $this->indexExists()) {
            $this->createIndex();
        }

        try {
            $exists = $this->client->indices()->exists([
                'index' => SystemConfigHelper::getIndexName(),
            ]);
            if (! $exists) {
                $this->client->indices()->create([
                    'index' => SystemConfigHelper::getIndexName(),
                ]);
            }
        } catch (\Exception $e) {
            $this->logger->error('MoriElasticSearch: Failed to save document to Elasticsearch', [
                'pdf' => $data['pdfPath'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        $params = [
            'index' => SystemConfigHelper::getIndexName(),
            'id' => $data['mediaId'],
            'body' => [
                'title' => $data['title'],
                'description' => $data['description'],
                'url' => $data['url'],
                'mediaId' => $data['mediaId'],
                'pdfPath' => $data['pdfPath'],
                'converted_at' => date('c'),
            ],
        ];

        $this->client->index($params);
    }

    public function delete(string $mediaId): bool
    {
        if (! $this->indexExists()) {
            return false;
        }

        $params = [
            'index' => SystemConfigHelper::getIndexName(),
            'id' => $mediaId,
        ];

        try {
            $this->client->delete($params);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('MoriElasticSearch: Failed to delete document to Elasticsearch', [
                'mediaId' => $mediaId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    public function exists(string $mediaId): bool
    {
        try {
            $params = [
                'index' => SystemConfigHelper::getIndexName(),
                'id' => $mediaId,
            ];

            $response = $this->client->get($params);
            return $response['found'] ?? false;
        } catch (\Exception $e) {
            $this->logger->error('MoriElasticSearch: ' . $e);
            return false;
        }
    }

    public function createIndex(): void
    {
        $params = [
            'index' => SystemConfigHelper::getIndexName(),
            'body' => [
                'settings' => [
                    'number_of_shards' => SystemConfigHelper::getNumberOfShards(),
                    'number_of_replicas' => SystemConfigHelper::getNumberOfReplicas(),
                ],
                'mappings' => [
                    'properties' => [
                        'title' => [
                            'type' => 'text',
                            'fields' => [
                                'keyword' => [
                                    'type' => 'keyword',
                                ],
                            ],
                        ],
                        'description' => [
                            'type' => 'text',
                            'fields' => [
                                'keyword' => [
                                    'type' => 'keyword',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->client->indices()->create($params);
        $this->logger->info('MoriElasticSearch: Index created successfully', [
            'index' => SystemConfigHelper::getIndexName(),
        ]);
    }

    public function indexExists(): bool
    {
        try {
            $params = [
                'index' => SystemConfigHelper::getIndexName(),
            ];
            return $this->client->indices()->exists($params);
        } catch (\Exception $e) {
            $this->logger->error('MoriElasticSearch: Failed to check/create index', [
                'index' => SystemConfigHelper::getIndexName(),
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
