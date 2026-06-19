<?php

declare(strict_types=1);

namespace MoriElasticSearch\Tests\Struct;

use MoriElasticSearch\Struct\PdfSearchResultStruct;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PdfSearchResultStruct::class)]
class PdfSearchResultStructTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $struct = new PdfSearchResultStruct();

        // Assert defaults
        $this->assertSame([], $struct->getResults());
        $this->assertSame(0, $struct->getTotal());
        $this->assertSame('', $struct->getSource());

        // Assert setters and fluid return interface (return $this)
        $results = ['item1', 'item2'];
        $this->assertSame($struct, $struct->setResults($results));
        $this->assertSame($results, $struct->getResults());

        $this->assertSame($struct, $struct->setTotal(2));
        $this->assertSame(2, $struct->getTotal());

        $this->assertSame($struct, $struct->setSource('elasticsearch'));
        $this->assertSame('elasticsearch', $struct->getSource());
    }

    public function testApiAlias(): void
    {
        $struct = new PdfSearchResultStruct();
        $this->assertSame('pdf_search_result', $struct->getApiAlias());
    }
}