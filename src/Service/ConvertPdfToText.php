<?php

declare(strict_types=1);

namespace MoriElasticSearch\Service;

use Smalot\PdfParser\Parser;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


class ConvertPdfToText
{
    private Storage $storage;
    private ParameterBagInterface $parameterBag;

    public function __construct(Storage $storage, ParameterBagInterface $parameterBag)
    {
        $this->storage = $storage;
        $this->parameterBag = $parameterBag;
    }

    public function pdfConvertToText($data): JsonResponse
    {
        try {
            $processedData = $this->processPdf($data);

            if ($this->storage->exists($processedData['mediaId'])) {
                return new JsonResponse([
                    'success' => true,
                    'message' => 'PDF already converted'
                ], 200);
            }

            $this->storage->save($processedData);

            return new JsonResponse([
                'success' => true,
                'message' => 'PDF converted successfully'
            ], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    private function processPdf(array $data): array
    {
        $projectRoot = $this->parameterBag->get('kernel.project_dir');
        $pdfPath = $projectRoot . '/public/' . $data['path'];

        $parser = new Parser();
        $description = trim($parser->parseFile($pdfPath)->getText());

        return [
            'title' => substr($description, 0, 30),
            'description' => $description,
            'url' => $data['path'],
            'pdfPath' => $pdfPath,
            'mediaId' => $data['mediaId'],
        ];
    }
}