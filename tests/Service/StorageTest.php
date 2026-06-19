<?php

declare(strict_types=1);

namespace MoriElasticSearch\Tests\Service;

use MoriElasticSearch\Service\DatabaseStorage;
use MoriElasticSearch\Service\ElasticsearchStorage;
use MoriElasticSearch\Service\Storage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(Storage::class)]
class StorageTest extends TestCase
{
    /**
     * @var DatabaseStorage&MockObject
     */
    private $databaseMock;

    /**
     * @var ElasticsearchStorage&MockObject
     */
    private $elasticsearchMock;

    private Storage $storage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->databaseMock = $this->createMock(DatabaseStorage::class);
        $this->elasticsearchMock = $this->createMock(ElasticsearchStorage::class);

        $this->storage = new Storage($this->databaseMock, $this->elasticsearchMock);
    }

    public function testSaveSavesToElasticSearchAndDatabaseWhenUpdateIsFalse(): void
    {
        $data = [
            'mediaId' => 'media-uuid-1',
            'update' => false,
        ];

        $this->databaseMock->expects($this->once())
            ->method('save')
            ->with($data);

        $this->elasticsearchMock->expects($this->once())
            ->method('save')
            ->with($data);

        $this->storage->save($data);
    }

    public function testSaveBypassesDatabaseWhenUpdateIsTrue(): void
    {
        $data = [
            'mediaId' => 'media-uuid-1',
            'update' => true,
        ];

        $this->databaseMock->expects($this->never())->method('save');

        $this->elasticsearchMock->expects($this->once())
            ->method('save')
            ->with($data);

        $this->storage->save($data);
    }

    public function testExistsReturnsTrueAndBypassesElasticsearchWhenFoundInDatabase(): void
    {
        $mediaId = 'media-uuid-1';

        $this->databaseMock->expects($this->once())
            ->method('exists')
            ->with($mediaId)
            ->willReturn(true);
        $this->elasticsearchMock->expects($this->never())->method('exists');

        $this->assertTrue($this->storage->exists($mediaId));
    }

    public function testExistsChecksElasticsearchWhenNotFoundInDatabase(): void
    {
        $mediaId = 'media-uuid-1';

        $this->databaseMock->expects($this->once())
            ->method('exists')
            ->with($mediaId)
            ->willReturn(false);

        $this->elasticsearchMock->expects($this->once())
            ->method('exists')
            ->with($mediaId)
            ->willReturn(true);

        $this->assertTrue($this->storage->exists($mediaId));
    }

    public function testExistsReturnsFalseWhenMissingEverywhere(): void
    {
        $mediaId = 'media-uuid-1';

        $this->databaseMock->expects($this->once())
            ->method('exists')
            ->with($mediaId)
            ->willReturn(false);

        $this->elasticsearchMock->expects($this->once())
            ->method('exists')
            ->with($mediaId)
            ->willReturn(false);

        $this->assertFalse($this->storage->exists($mediaId));
    }

    public function testDeleteReturnsTrueWhenPurgedFromBothSystems(): void
    {
        $mediaId = 'media-uuid-1';

        $this->elasticsearchMock->expects($this->once())
            ->method('delete')
            ->with($mediaId)
            ->willReturn(true);

        $this->databaseMock->expects($this->once())
            ->method('delete')
            ->with($mediaId)
            ->willReturn(true);

        $this->assertTrue($this->storage->delete($mediaId));
    }
    public function testDeleteShortCircuitsAndBypassesDatabaseIfElasticsearchDeletionFails(): void
    {
        $mediaId = 'media-uuid-1';

        $this->elasticsearchMock->expects($this->once())
            ->method('delete')
            ->with($mediaId)
            ->willReturn(false);
        $this->databaseMock->expects($this->never())->method('delete');

        $this->assertFalse($this->storage->delete($mediaId));
    }

    public function testDeleteReturnsFalseIfDatabaseDeletionFails(): void
    {
        $mediaId = 'media-uuid-1';

        $this->elasticsearchMock->expects($this->once())
            ->method('delete')
            ->with($mediaId)
            ->willReturn(true);

        $this->databaseMock->expects($this->once())
            ->method('delete')
            ->with($mediaId)
            ->willReturn(false);

        $this->assertFalse($this->storage->delete($mediaId));
    }
}