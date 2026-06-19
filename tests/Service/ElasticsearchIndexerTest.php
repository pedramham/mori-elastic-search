<?php

declare(strict_types=1);

namespace MoriElasticSearch\Tests\Service;

use MoriElasticSearch\Core\Content\PdfElasticSearch\PdfElasticSearchEntity;
use MoriElasticSearch\Service\ElasticsearchIndexer;
use MoriElasticSearch\Service\ElasticsearchStorage;
use OpenSearch\Client;
use OpenSearch\Namespaces\IndicesNamespace;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;

#[CoversClass(ElasticsearchIndexer::class)]
class ElasticsearchIndexerTest extends TestCase
{
    use IntegrationTestBehaviour;

    /** @var EntityRepository&MockObject */
    private $pdfRepositoryMock;

    /** @var ElasticsearchStorage&MockObject */
    private $elasticsearchStorageMock;

    private LoggerInterface $loggerStub;
    private Client $clientMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pdfRepositoryMock = $this->createMock(EntityRepository::class);
        $this->elasticsearchStorageMock = $this->createMock(ElasticsearchStorage::class);
        $this->loggerStub = $this->createStub(LoggerInterface::class);
        $this->clientMock = $this->createStub(Client::class);
    }

    public function testReindexDeletesOldIndexIfExistsAndCreatesNewIndex(): void
    {
        $indicesMock = $this->createMock(IndicesNamespace::class);
        $indicesMock->expects($this->once())
            ->method('delete');

        $this->clientMock->method('indices')->willReturn($indicesMock);
        $this->elasticsearchStorageMock->expects($this->once())
            ->method('indexExists')
            ->willReturn(true);
        $this->elasticsearchStorageMock->expects($this->once())
            ->method('createIndex');

        $emptySearchResult = new EntitySearchResult(
            'pdf_elastic_search',
            0,
            new EntityCollection([]),
            null,
            new Criteria(),
            Context::createDefaultContext()
        );

        $this->pdfRepositoryMock->expects($this->once())
            ->method('search')
            ->willReturn($emptySearchResult);

        $indexer = new ElasticsearchIndexer(
            $this->pdfRepositoryMock,
            $this->elasticsearchStorageMock,
            $this->loggerStub,
            $this->clientMock
        );

        $indexer->reindex();
    }

    public function testReindexLogsAndRethrowsExceptionOnFailure(): void
    {
        $this->elasticsearchStorageMock->expects($this->once())
            ->method('indexExists')
            ->willThrowException(new \Exception('Connection lost'));

        $this->pdfRepositoryMock->expects($this->never())
            ->method('search');

        $indexer = new ElasticsearchIndexer(
            $this->pdfRepositoryMock,
            $this->elasticsearchStorageMock,
            $this->loggerStub,
            $this->clientMock
        );

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Connection lost');

        $indexer->reindex();
    }

    public function testSaveInElasticsearchAndDatabase(): void
    {
        $indicesMock = $this->createMock(IndicesNamespace::class);
        $indicesMock->expects($this->never())->method('delete');

        $this->clientMock->method('indices')->willReturn($indicesMock);
        $this->elasticsearchStorageMock->expects($this->once())
            ->method('indexExists')
            ->willReturn(false);
        $this->elasticsearchStorageMock->expects($this->once())
            ->method('createIndex');

        $pdfEntity = new PdfElasticSearchEntity();
        $pdfEntity->setId(Uuid::randomHex()); // EntityIdTrait needs this
        $pdfEntity->setMediaId('media-id-abc');
        $pdfEntity->setTitle('Test PDF Title');
        $pdfEntity->setDescription('Test description contents');
        $pdfEntity->setPath('files/document.pdf');
        $pdfEntity->setActive(true);

        $batchSearchResult = new EntitySearchResult(
            'pdf_elastic_search',
            1,
            new EntityCollection([$pdfEntity]),
            null,
            new Criteria(),
            Context::createDefaultContext()
        );

        $this->pdfRepositoryMock->expects($this->once())
            ->method('search')
            ->willReturn($batchSearchResult);

        $this->elasticsearchStorageMock->expects($this->once())
            ->method('save')
            ->with([
                'mediaId' => 'media-id-abc',
                'title' => 'Test PDF Title',
                'description' => 'Test description contents',
                'url' => 'files/document.pdf',
                'pdfPath' => 'files/document.pdf',
            ]);

        $indexer = new ElasticsearchIndexer(
            $this->pdfRepositoryMock,
            $this->elasticsearchStorageMock,
            $this->loggerStub,
            $this->clientMock
        );

        $indexer->reindex();
    }
}