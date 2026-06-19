<?php

declare(strict_types=1);

namespace MoriElasticSearch\Tests\Service;

use MoriElasticSearch\Service\ConvertPdfToText;
use MoriElasticSearch\Service\Storage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Kernel;
use Smalot\PdfParser\Parser;

#[CoversClass(ConvertPdfToText::class)]
class ConvertPdfToTextTest extends TestCase
{
    private Storage|MockObject $storageMock;
    private ConvertPdfToText $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->storageMock = $this->createMock(Storage::class);

        // Use createStub() instead of createMock() to resolve the two PHPUnit notices
        $this->kernelMock = $this->createStub(Kernel::class);
        $this->kernelMock->method('getProjectDir')->willReturn('/var/www/html/project');

        $this->service = new ConvertPdfToText($this->storageMock, $this->kernelMock);
    }

    public function testSaveSuccessfullyUpdatesPdf(): void
    {
        $data = [
            'title' => 'title',
            'path' => 'path.pdf',
            'mediaId' => 'media-uuid',
            'update' => true,
            'description' => 'description'
        ];

        $this->storageMock->expects($this->once())
            ->method('exists')
            ->with('media-uuid')
            ->willReturn(false);


        $this->storageMock->expects($this->once())
            ->method('save')
            ->with($this->callback(function (array $processedData) {
                $this->assertEquals('title', $processedData['title']);
                $this->assertEquals('description', $processedData['description']);
                $this->assertEquals('path.pdf', $processedData['url']);
                $this->assertEquals('media-uuid', $processedData['mediaId']);
                $this->assertTrue($processedData['update']);
                return true;
            }));

        $response = $this->service->save($data);
        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertTrue($responseData['success']);
        $this->assertEquals('PDF converted successfully', $responseData['message']);
    }



    public function testSaveReturnsAlreadyConvertedResponseWhenPdfExists(): void
    {
        $documentMock = $this->createStub(\Smalot\PdfParser\Document::class);
        $documentMock->method('getText')->willReturn('Extracted Test Text');

        $parserMock = $this->createStub(Parser::class);
        $parserMock->method('parseFile')
            ->willReturn($documentMock);

        $this->service = new ConvertPdfToText($this->storageMock, $this->kernelMock, $parserMock);

        $data = [
            'path' => 'any-path.pdf',
            'mediaId' => 'media-uuid',
            'update' => false,
            'title' => 'My Title'
        ];

        $this->storageMock->expects($this->once())
            ->method('exists')
            ->with('media-uuid')
            ->willReturn(true);

        $this->storageMock->expects($this->never())->method('save');
        $response = $this->service->save($data);
        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertTrue($responseData['success']);
        $this->assertEquals('PDF already converted', $responseData['message']);
    }

    public function testSaveHandlesParserExceptionAndReturns500(): void
    {
        // 1. Configure the stub Parser to throw a standard Exception
        $parserMock = $this->createStub(\Smalot\PdfParser\Parser::class);
        $parserMock->method('parseFile')
            ->willThrowException(new \Exception('Parser failed to read PDF'));

        // 2. Inject the failing stub parser
        $this->service = new ConvertPdfToText($this->storageMock, $this->kernelMock, $parserMock);

        $data = [
            'path' => 'any-path.pdf',
            'mediaId' => 'media-uuid',
            'update' => false,
            'title' => 'My Title'
        ];

        // 3. Since the exception is thrown during processPdf, storage methods should never run
        $this->storageMock->expects($this->never())->method('exists');
        $this->storageMock->expects($this->never())->method('save');

        // 4. Execute the method
        $response = $this->service->save($data);

        // 5. Assertions
        $this->assertEquals(500, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertFalse($responseData['success']);
        $this->assertEquals('Error: Parser failed to read PDF', $responseData['message']);
    }

    public function testPdfDeleteReturnsErrorWhenMediaIdIsEmpty(): void
    {
        $this->storageMock->expects($this->never())
            ->method('delete');

        $response = $this->service->pdfDelete('');

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string)$response->getContent(), true);
        $this->assertFalse($responseData['success']);
        $this->assertEquals('mediaId is required', $responseData['message']);
    }

    public function testPdfDeleteReturnsSuccessWhenDeletionSucceeds(): void
    {
        $mediaId = 'valid-media-uuid';
        $this->storageMock->expects($this->once())
            ->method('delete')
            ->with($mediaId)
            ->willReturn(true);

        $response = $this->service->pdfDelete($mediaId);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string)$response->getContent(), true);
        $this->assertTrue($responseData['success']);
        $this->assertEquals('pdf deleted', $responseData['message']);
    }

    public function testPdfDeleteReturnsErrorWhenDeletionFails(): void
    {
        $mediaId = 'failed-media-uuid';
        $this->storageMock->expects($this->once())
            ->method('delete')
            ->with($mediaId)
            ->willReturn(false);

        $response = $this->service->pdfDelete($mediaId);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string)$response->getContent(), true);
        $this->assertFalse($responseData['success']);
        $this->assertEquals('something happened wrong try again', $responseData['message']);
    }
}