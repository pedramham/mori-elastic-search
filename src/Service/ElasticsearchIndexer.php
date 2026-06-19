<?php

namespace MoriElasticSearch\Service;

use MoriElasticSearch\Config\SystemConfigHelper;
use OpenSearch\Client;
use OpenSearch\ClientBuilder;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;

class ElasticsearchIndexer
{
    private Client $client;

    private EntityRepository $pdfRepository;

    private ElasticsearchStorage $elasticsearchStorage;

    private LoggerInterface $logger;

    public function __construct(
        EntityRepository $pdfRepository,
        ElasticsearchStorage $elasticsearchStorage,
        LoggerInterface $logger,
        ?Client $client = null // Optional injection
    ) {
        $this->client = $client ?? ClientBuilder::create()->setHosts([SystemConfigHelper::getHost()])->build();
        $this->pdfRepository = $pdfRepository;
        $this->elasticsearchStorage = $elasticsearchStorage;
        $this->logger = $logger;
    }

    public function reindex(): void
    {
        $context = Context::createDefaultContext();
        $indexName = SystemConfigHelper::getIndexName();

        $this->logger->info('MoriElasticSearch: Starting reindex', [
            'index' => $indexName,
        ]);

        try {
            if ($this->elasticsearchStorage->indexExists()) {
                $this->client->indices()->delete([
                    'index' => $indexName,
                ]);
                $this->logger->info('MoriElasticSearch: Deleted old index');
            }

            $this->elasticsearchStorage->createIndex();
            $this->logger->info('MoriElasticSearch: Created new index');

            $count = $this->reindexDocuments($context);

            $this->logger->info('MoriElasticSearch: Reindex completed successfully', [
                'index' => $indexName,
                'total_documents' => $count,
            ]);
        } catch (\Exception $e) {
            $this->logger->error('MoriElasticSearch: Reindex failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    private function reindexDocuments(Context $context): int
    {
        $total = 0;
        $limit = 100;
        $offset = 0;

        while (true) {
            // Fetch PDFs from database in batches
            $pdfs = $this->getPdfsFromDatabase($context, $offset, $limit);

            if (empty($pdfs)) {
                break;
            }

            // Index each PDF
            foreach ($pdfs as $pdf) {
                $this->elasticsearchStorage ->save([
                    'mediaId' => $pdf->getMediaId(),
                    'title' => $pdf->getTitle() ?? '',
                    'description' => $pdf->getDescription() ?? '',
                    'url' => $pdf->getPath() ?? '',
                    'pdfPath' => $pdf->getPath() ?? '',
                ]);
                $total++;
            }

            $this->logger->info('MoriElasticSearch: Reindexed batch', [
                'offset' => $offset,
                'batch_size' => count($pdfs),
                'total' => $total,
            ]);

            if (count($pdfs) < $limit) {
                break;
            }

            $offset += $limit;
        }

        return $total;
    }

    private function getPdfsFromDatabase(Context $context, int $offset, int $limit): array
    {
        $criteria = new Criteria();
        $criteria->setOffset($offset);
        $criteria->setLimit($limit);

        $result = $this->pdfRepository->search($criteria, $context);
        return $result->getElements();
    }
}
