<?php

namespace MoriElasticSearch\Struct;

use Shopware\Core\Framework\Struct\Struct;

class PdfSearchResultStruct extends Struct
{
    protected array $results = [];

    protected int $total = 0;

    protected string $source = '';

    public function getResults(): array
    {
        return $this->results;
    }

    public function setResults(array $results): self
    {
        $this->results = $results;
        return $this;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function setTotal(int $total): self
    {
        $this->total = $total;
        return $this;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function setSource(string $source): self
    {
        $this->source = $source;
        return $this;
    }

    public function getApiAlias(): string
    {
        return 'pdf_search_result';
    }
}
