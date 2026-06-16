<?php

declare(strict_types=1);

namespace MoriElasticSearch\Command;

use OpenSearch\ClientBuilder;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'mori-commands:convert-pdf',
    description: 'convert pdf to text.',
)]
class MoriElasticSearchCommand extends Command
{
    private EntityRepository $pdfElasticSearchRepository;

    private EntityRepository $mediaRepository;

    public function __construct(EntityRepository $pdfElasticSearchRepository, EntityRepository $mediaRepository)
    {
        parent::__construct();
        $this->pdfElasticSearchRepository = $pdfElasticSearchRepository;
        $this->mediaRepository = $mediaRepository;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Convert a PDF to text')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $context = Context::createDefaultContext();
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('id', "019ecf05fe0375c09764fd2bcd86dfa9"));
        $criteria->setLimit(1);

        $ids = $this->mediaRepository->searchIds($criteria, $context)->getIds();
        //  $record = $result->first();

        //  dd($ids);
        if ($record) {
            $this->repository->delete([
                [
                    'id' => $record->getId(),
                ],
            ], $context);
        }

        $data = [
            'title' => 'test',
            'description' => 'description',
            'url' => 'url',
            'mediaId' => 'mediaId',
            'pdfPath' => 'test',
        ];
        $this->saveToElasticsearch($data);

        $parser = new \Smalot\PdfParser\Parser();
        $scriptDir = dirname(__FILE__);
        $textContent = $parser->parseFile($scriptDir . '/test.pdf');

        $context = Context::createDefaultContext();

        $this->pdfElasticSearchRepository->create([
            [
                'title' => 'Example product',
                'description' => $textContent->getText(),
            ],
        ], $context);

        return 0;
    }

    private function saveToElasticsearch(array $data): void
    {
        try {
            $client = ClientBuilder::create()
                ->setHosts(['http://localhost:9200'])
                ->build();

            $indexName = 'sw_pdf_documents_v1';

            $params = [
                'index' => $indexName,
                'id' => $data['mediaId'],
                'body' => [
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'url' => $data['url'],
                    'mediaId' => $data['mediaId'],
                    'pdfPath' => $data['pdfPath'],
                    'converted_at' => date('c'),
                ],
            ];

            $client->index($params);
        } catch (\Exception $e) {
        }
    }
}
