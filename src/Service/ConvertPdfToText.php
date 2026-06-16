<?php

declare(strict_types=1);

namespace MoriElasticSearch\Service;

use Shopware\Core\Kernel;
use Smalot\PdfParser\Parser;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\HttpFoundation\JsonResponse;

class ConvertPdfToText
{
    private Storage $storage;

    private string $projectDir;

    public function __construct(Storage $storage, Kernel $kernel)
    {
        $this->storage = $storage;
        $this->projectDir = $kernel->getProjectDir();
    }

    public function save(array $data): JsonResponse
    {
        try {
            $processedData = $this->processPdf($data);
            if ($this->storage->exists($processedData['mediaId']) && ! $processedData['update']) {
                return new JsonResponse([
                    'success' => true,
                    'message' => 'PDF already converted',
                ], 200);
            }

            $this->storage->save($processedData);

            return new JsonResponse([
                'success' => true,
                'message' => 'PDF converted successfully',
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function processPdf(array $data): array
    {
        $pdfPath = Path::join($this->projectDir, 'public', $data['path']);

        if (! $data['update']) {
            $parser = new Parser();
            $description = trim($parser->parseFile($pdfPath)->getText());
        } else {
            $description = trim($data['description']);
        }

        $title = $data['title'] ?? substr($description, 0, 30);

        return [
            'title' => $title,
            'description' => $description,
            'url' => $data['path'],
            'pdfPath' => $pdfPath,
            'mediaId' => $data['mediaId'],
            'update' => $data['update'],
        ];
    }

    public function pdfDelete(string $mediaId): JsonResponse
    {
        if (! $mediaId) {
            return new JsonResponse([
                'success' => false,
                'message' => 'mediaId is required',
            ], 200);
        }

        if ($this->storage->delete($mediaId)) {
            return new JsonResponse([
                'success' => true,
                'message' => 'pdf deleted',
            ], 200);
        };

        return new JsonResponse([
            'success' => false,
            'message' => 'something happened wrong try again',
        ], 200);
    }
}
