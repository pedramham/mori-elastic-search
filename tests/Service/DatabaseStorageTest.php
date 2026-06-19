<?php

declare(strict_types=1);

namespace MoriElasticSearch\Tests\Service;

use MoriElasticSearch\Service\DatabaseStorage;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;

class DatabaseStorageTest extends TestCase
{

    use IntegrationTestBehaviour;

    public function testDatabaseStorageLifecycle(): void
    {
        $repository = static::getContainer()->get('pdf_elastic_search.repository');
        $storage = new DatabaseStorage($repository);

        $mediaId = Uuid::randomHex();
        $data = [
            'title' => 'Test Integration Title',
            'mediaId' => $mediaId,
            'description' => 'Test Integration Description',
            'url' => 'public/path/test.pdf',
        ];

        $this->assertFalse($storage->exists($mediaId));

        $storage->save($data);

        $this->assertTrue($storage->exists($mediaId));

        $deleted = $storage->delete($mediaId);
        $this->assertTrue($deleted);

        $this->assertFalse($storage->exists($mediaId));
    }

    public function testDeleteReturnsFalseWhenMediaIdDoesNotExist(): void
    {
        $repository = static::getContainer()->get('pdf_elastic_search.repository');
        $storage = new DatabaseStorage($repository);

        $nonExistentMediaId = Uuid::randomHex();

        $deleted = $storage->delete($nonExistentMediaId);
        $this->assertFalse($deleted);
    }


    public function testDeleteReturnsFalseWhenExceptionIsThrown(): void
    {
        $repositoryMock = $this->createStub(EntityRepository::class);
        $repositoryMock->method('searchIds')
            ->willThrowException(new \Exception('Forced mock database exception'));
        $storage = new DatabaseStorage($repositoryMock);
        $deleted = $storage->delete('any-media-uuid');

        $this->assertFalse($deleted);
    }
}