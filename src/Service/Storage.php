<?php

declare(strict_types=1);

namespace MoriElasticSearch\Service;


class Storage implements ConvertPdfInterface
{
    private DatabaseStorage $database;
    private ElasticsearchStorage $elasticsearch;

    public function __construct(
        DatabaseStorage $database,
        ElasticsearchStorage $elasticsearch
    ) {
        $this->database = $database;
        $this->elasticsearch = $elasticsearch;
    }

    public function save(array $data): void
    {
        $this->database->save($data);
        $this->elasticsearch->save($data);
    }

    public function exists(string $mediaId): bool
    {
        if ($this->database->exists($mediaId)) {
            return true;
        }

        return $this->elasticsearch->exists($mediaId);
    }
}