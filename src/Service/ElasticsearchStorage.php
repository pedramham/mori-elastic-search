<?php

declare(strict_types=1);

namespace MoriElasticSearch\Service;

use OpenSearch\Client;
use OpenSearch\ClientBuilder;
use Psr\Log\LoggerInterface;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class ElasticsearchStorage implements ConvertPdfInterface
{
    private Client $client;
    private string $indexName;
    private SystemConfigService $systemConfig;
    private LoggerInterface $logger;

    public function __construct(SystemConfigService $systemConfig, LoggerInterface $logger)
    {
        $this->systemConfig = $systemConfig;
        $host = $systemConfig->get('MoriElasticSearch.config.elasticSearchHost') ?? 'http://localhost:9200';
        $this->client = ClientBuilder::create()->setHosts([$host])->build();
        $this->indexName = $systemConfig->get('MoriElasticSearch.config.elasticSearchIndexName', ) ?? 'sw_pdf_documents_v1';
        $this->logger = $logger;
    }

    public function save(array $data): void
    {
        if (!$this->indexExists()) {
            $this->createIndex();
        }
        // Create index if not exists (silent check)
        try {
            $exists = $this->client->indices()->exists(['index' => $this->indexName]);
            if (!$exists) {
                $this->client->indices()->create(['index' => $this->indexName]);
            }
        } catch (\Exception $e) {
            $this->logger->error('MoriElasticSearch: Failed to save document to Elasticsearch', [
                'pdf' => $data['pdfPath'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        $params = [
            'index' => $this->indexName,
            'id' => $data['mediaId'],
            'body' => [
                'title' => $data['title'],
                'description' => $data['description'],
                'url' => $data['url'],
                'mediaId' => $data['mediaId'],
                'pdfPath' => $data['pdfPath'],
                'converted_at' => date('c')
            ]
        ];

        $this->client->index($params);
    }

    public function exists(string $mediaId): bool
    {
        try {
            $params = [
                'index' => $this->indexName,
                'id' => $mediaId
            ];

            $response = $this->client->get($params);
            return $response['found'] ?? false;
        } catch (\Exception $e) {
            $this->logger->error('MoriElasticSearch: '. $e);
            return false;
        }
    }

    private function createIndex(): void
    {
        $searchNumberOfShards = $this->systemConfig->get('MoriElasticSearch.config.elasticSearchNumberOfShards') ?? 1;
        $searchNumberOfReplicas = $this->systemConfig->get('MoriElasticSearch.config.elasticSearchNumberOfReplicas') ?? 0;
        $params = [
            'index' => $this->indexName,
            'body' => [
                'settings' => [
                    'number_of_shards' => $searchNumberOfShards,
                    'number_of_replicas' => $searchNumberOfReplicas
                ],
                'mappings' => [
                    'properties' => [
                        'title' => [
                            'type' => 'text',
                            'fields' => [
                                'keyword' => ['type' => 'keyword']
                            ]
                        ],
                        'description' => [
                            'type' => 'text',
                            'fields' => [
                                'keyword' => ['type' => 'keyword']
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $this->client->indices()->create($params);
        $this->logger->info('MoriElasticSearch: Index created successfully', ['index' => $this->indexName]);
    }

    private function indexExists(): bool
    {
        try {
            $params = ['index' => $this->indexName];
            return $this->client->indices()->exists($params);
        } catch (\Exception $e) {
            $this->logger->error('MoriElasticSearch: Failed to check/create index', [
                'index' => $this->indexName,
                'error' => $e->getMessage()
            ]);
        }
    }

}