<?php

declare(strict_types=1);

namespace MoriElasticSearch\Tests\Service;

use MoriElasticSearch\Service\ElasticsearchStorage;
use OpenSearch\Client;
use OpenSearch\Namespaces\IndicesNamespace;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

#[CoversClass(ElasticsearchStorage::class)]
class ElasticsearchStorageTest extends TestCase
{
    private LoggerInterface $loggerStub;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loggerStub = $this->createStub(LoggerInterface::class);
    }

    public function testIndexExistsReturnsTrueWhenIndexExists(): void
    {
        $indicesMock = $this->createMock(IndicesNamespace::class);
        $indicesMock->expects($this->once())
            ->method('exists')
            ->willReturn(true);

        $clientMock = $this->createStub(Client::class);
        $clientMock->method('indices')->willReturn($indicesMock);

        $storage = new ElasticsearchStorage($this->loggerStub, $clientMock);

        $this->assertTrue($storage->indexExists());
    }

    public function testIndexExistsCatchesExceptionAndReturnsFalse(): void
    {
        $indicesMock = $this->createMock(IndicesNamespace::class);
        $indicesMock->expects($this->once())
            ->method('exists')
            ->willThrowException(new \Exception('Connection timed out'));

        $clientMock = $this->createStub(Client::class);
        $clientMock->method('indices')->willReturn($indicesMock);

        $storage = new ElasticsearchStorage($this->loggerStub, $clientMock);

        $this->assertFalse($storage->indexExists());
    }

    public function testCreateIndexCallsCreateOnIndices(): void
    {
        $indicesMock = $this->createMock(IndicesNamespace::class);
        $indicesMock->expects($this->once())
            ->method('create')
            ->with($this->callback(function (array $params) {
                $this->assertArrayHasKey('index', $params);
                $this->assertArrayHasKey('body', $params);
                $this->assertArrayHasKey('settings', $params['body']);
                $this->assertArrayHasKey('mappings', $params['body']);
                return true;
            }));

        $clientMock = $this->createStub(Client::class);
        $clientMock->method('indices')->willReturn($indicesMock);

        $storage = new ElasticsearchStorage($this->loggerStub, $clientMock);

        $storage->createIndex();
    }

    public function testExistsReturnsTrueWhenDocumentFound(): void
    {
        $mediaId = 'test-media-123';

        $clientMock = $this->createMock(Client::class);
        $clientMock->expects($this->once())
            ->method('get')
            ->with($this->callback(function (array $params) use ($mediaId) {
                $this->assertSame($mediaId, $params['id']);
                return true;
            }))
            ->willReturn(['found' => true]);

        $storage = new ElasticsearchStorage($this->loggerStub, $clientMock);

        $this->assertTrue($storage->exists($mediaId));
    }

    public function testExistsReturnsFalseWhenExceptionIsThrown(): void
    {
        $clientMock = $this->createMock(Client::class);
        $clientMock->expects($this->once())
            ->method('get')
            ->willThrowException(new \Exception('Unreachable node'));

        $storage = new ElasticsearchStorage($this->loggerStub, $clientMock);

        $this->assertFalse($storage->exists('test-media-123'));
    }

    public function testDeleteReturnsFalseIfIndexDoesNotExist(): void
    {
        $indicesMock = $this->createMock(IndicesNamespace::class);
        $indicesMock->expects($this->once())
            ->method('exists')
            ->willReturn(false); // Index missing

        $clientMock = $this->createMock(Client::class);
        $clientMock->method('indices')->willReturn($indicesMock);
        $clientMock->expects($this->never())->method('delete');

        $storage = new ElasticsearchStorage($this->loggerStub, $clientMock);

        $this->assertFalse($storage->delete('test-media-123'));
    }

    public function testDeleteIssuesDeleteOnSuccessfulRun(): void
    {
        $mediaId = 'test-media-123';

        $indicesMock = $this->createMock(IndicesNamespace::class);
        $indicesMock->expects($this->once())
            ->method('exists')
            ->willReturn(true); // Index exists

        $clientMock = $this->createMock(Client::class);
        $clientMock->method('indices')->willReturn($indicesMock);

        $clientMock->expects($this->once())
            ->method('delete')
            ->with($this->callback(function (array $params) use ($mediaId) {
                $this->assertSame($mediaId, $params['id']);
                return true;
            }))
            ->willReturn(['result' => 'deleted']);

        $storage = new ElasticsearchStorage($this->loggerStub, $clientMock);

        $this->assertTrue($storage->delete($mediaId));
    }

    public function testDeleteReturnsFalseWhenExceptionIsThrown(): void
    {
        $indicesMock = $this->createMock(IndicesNamespace::class);
        $indicesMock->expects($this->once())
            ->method('exists')
            ->willReturn(true);

        $clientMock = $this->createMock(Client::class);
        $clientMock->method('indices')->willReturn($indicesMock);
        $clientMock->expects($this->once())
            ->method('delete')
            ->willThrowException(new \Exception('Delete blocked'));

        $storage = new ElasticsearchStorage($this->loggerStub, $clientMock);

        $this->assertFalse($storage->delete('test-media-123'));
    }

    public function testSaveIndexesDocumentSuccessfully(): void
    {
        $data = [
            'mediaId' => 'media-uuid-abc',
            'title' => 'My Document Title',
            'description' => 'Extractable text description',
            'url' => 'public/path.pdf',
            'pdfPath' => 'public/path.pdf',
        ];

        $indicesMock = $this->createMock(IndicesNamespace::class);
        $indicesMock->expects($this->exactly(2))
            ->method('exists')
            ->willReturn(true);

        $clientMock = $this->createMock(Client::class);
        $clientMock->method('indices')->willReturn($indicesMock);

        $clientMock->expects($this->once())
            ->method('index')
            ->with($this->callback(function (array $params) use ($data) {
                $this->assertSame($data['mediaId'], $params['id']);
                $this->assertSame($data['title'], $params['body']['title']);
                $this->assertSame($data['description'], $params['body']['description']);
                $this->assertSame($data['url'], $params['body']['url']);
                $this->assertSame($data['pdfPath'], $params['body']['pdfPath']);
                $this->assertArrayHasKey('converted_at', $params['body']);
                return true;
            }));

        $storage = new ElasticsearchStorage($this->loggerStub, $clientMock);

        $storage->save($data);
    }
}