<?php

declare(strict_types=1);

namespace MoriElasticSearch\Controller;

use MoriElasticSearch\Service\ConvertPdfToText;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['api']])]
class PdfConvertController extends AbstractController
{
    private ConvertPdfToText $convertPdfToText;

    public function __construct(ConvertPdfToText $convertPdfToText)
    {
        $this->convertPdfToText = $convertPdfToText;
    }

    #[\Symfony\Component\Routing\Attribute\Route(
        path: '/api/_action/pdf/convert-to-text',
        name: 'api.action.pdf.convert-to-text',
        methods: ['POST']
    )]
    public function convertToText(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        return $this->convertPdfToText->pdfConvertToText($data);
    }
}