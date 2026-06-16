<?php

declare(strict_types=1);

namespace MoriElasticSearch\Service;

interface ConvertPdfInterface
{
    public function save(array $data): void;

    public function exists(string $mediaId): bool;

    public function delete(string $mediaId): bool;
}
