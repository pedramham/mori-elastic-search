<?php

declare(strict_types=1);

namespace MoriElasticSearch\Tests\Controller;

use MoriElasticSearch\Controller\PdfConvertController;
use MoriElasticSearch\Service\ConvertPdfToText;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class PdfConvertControllerTest extends TestCase
{
    private $convertPdfToText;
    private $controller;

    protected function setUp(): void
    {
        $this->convertPdfToText = $this->createMock(ConvertPdfToText::class);
        $this->controller = new PdfConvertController($this->convertPdfToText);
    }

    public function testConvertToText(): void
    {
        $requestData = [
            'mediaId' => 'test-media-id',
            'path' => '/test.pdf',
            'update' => false
        ];

        $request = new Request([], [], [], [], [], [], json_encode($requestData));
        $request->headers->set('Content-Type', 'application/json');

        $expectedResponse = new JsonResponse([
            'success' => true,
            'message' => 'PDF converted successfully'
        ]);

        $this->convertPdfToText
            ->expects($this->once())
            ->method('save')
            ->with($requestData)
            ->willReturn($expectedResponse);

        $response = $this->controller->convertToText($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testDeleteSuccess(): void
    {
        $requestData = ['mediaId' => 'test-media-id'];
        $request = new Request([], [], [], [], [], [], json_encode($requestData));
        $request->headers->set('Content-Type', 'application/json');

        $expectedResponse = new JsonResponse([
            'success' => true,
            'message' => 'PDF deleted successfully'
        ]);

        $this->convertPdfToText
            ->expects($this->once())
            ->method('pdfDelete')
            ->with('test-media-id')
            ->willReturn($expectedResponse);

        $response = $this->controller->delete($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testDeleteWithoutMediaId(): void
    {
        $requestData = [];
        $request = new Request([], [], [], [], [], [], json_encode($requestData));
        $request->headers->set('Content-Type', 'application/json');

        $expectedResponse = new JsonResponse([
            'success' => false,
            'message' => 'MediaId is required'
        ], 400);

        $this->convertPdfToText
            ->expects($this->once())
            ->method('pdfDelete')
            ->with('') // Expect the empty string fallback
            ->willReturn($expectedResponse);

        $response = $this->controller->delete($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
    }
}