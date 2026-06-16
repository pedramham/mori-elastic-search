<?php

declare(strict_types=1);

namespace MoriElasticSearch\Command;

use MoriElasticSearch\Service\ElasticsearchIndexer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'mori-commands:reindex',
    description: 'Reindex all PDF documents in Elasticsearch'
)]
class ReindexCommand extends Command
{
    private ElasticsearchIndexer $elasticsearchIndexer;

    public function __construct(ElasticsearchIndexer $elasticsearchIndexer)
    {
        parent::__construct();
        $this->elasticsearchIndexer = $elasticsearchIndexer;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        try {
            $io->section('Starting reindex...');

            $this->elasticsearchIndexer->reindex();

            $io->success("Reindex completed!");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Reindex failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
