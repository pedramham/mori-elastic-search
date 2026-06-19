<?php

declare(strict_types=1);

namespace MoriElasticSearch\Controller;

use MoriElasticSearch\Service\ConvertPdfToText;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: [
    '_routeScope' => ['api'],
])]
class PdfConvertController extends AbstractController
{
    private ConvertPdfToText $convertPdfToText;

    public function __construct(ConvertPdfToText $convertPdfToText)
    {
        $this->convertPdfToText = $convertPdfToText;
    }

    #[\Symfony\Component\Routing\Attribute\Route(
        path: '/api/v1/elasticsearch/mori_pdf/upsert',
        name: 'api.v1.elasticsearch.mori_pdf.upsert',
        methods: ['POST']
    )]
    public function convertToText(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        return $this->convertPdfToText->save($data);
    }

    #[\Symfony\Component\Routing\Attribute\Route(
        path: '/api/v1/elasticsearch/mori_pdf/delete',
        name: 'api.action.pdf.elasticsearch.delete',
        methods: ['DELETE']
    )]
    public function delete(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $mediaId = $data['mediaId'] ?? '';
        return $this->convertPdfToText->pdfDelete($mediaId);
    }
}
