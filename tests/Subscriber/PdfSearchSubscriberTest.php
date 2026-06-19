<?php

declare(strict_types=1);

namespace MoriElasticSearch\Tests\Subscriber;

use MoriElasticSearch\Subscriber\PdfSearchSubscriber;
use MoriElasticSearch\Service\Search\SearchCoordinator;
use MoriElasticSearch\Struct\PdfSearchResultStruct;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\Suggest\SuggestPage;
use Shopware\Storefront\Page\Suggest\SuggestPageLoadedEvent;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(PdfSearchSubscriber::class)]
class PdfSearchSubscriberTest extends TestCase
{
    public function testGetSubscribedEventsReturnsCorrectMapping(): void
    {
        $events = PdfSearchSubscriber::getSubscribedEvents();

        $this->assertArrayHasKey(SuggestPageLoadedEvent::class, $events);
        $this->assertSame('onSuggestPageLoaded', $events[SuggestPageLoadedEvent::class]);
    }

    public function testOnSuggestPageLoadedReturnsEarlyIfNoSearchKeyword(): void
    {
        $searchCoordinatorMock = $this->createMock(SearchCoordinator::class);
        $searchCoordinatorMock->expects($this->never())->method('search');

        $subscriber = new PdfSearchSubscriber($searchCoordinatorMock);

        $request = new Request();
        $page = new SuggestPage();

        // Use createStub() to avoid the PHPUnit Notice
        $salesChannelContextStub = $this->createStub(SalesChannelContext::class);

        $event = new SuggestPageLoadedEvent($page, $salesChannelContextStub, $request);

        $subscriber->onSuggestPageLoaded($event);

        $this->assertNull($page->getExtension('pdfResults'));
    }

    public function testOnSuggestPageLoadedAppendsResultsWhenKeywordExists(): void
    {
        $keyword = 'sample pdf search';
        $request = new Request(['search' => $keyword]);
        $page = new SuggestPage();

        // Use createStub() for both to avoid the PHPUnit Notices
        $contextStub = $this->createStub(Context::class);
        $salesChannelContextStub = $this->createStub(SalesChannelContext::class);
        $salesChannelContextStub->method('getContext')->willReturn($contextStub);

        $resultStruct = new PdfSearchResultStruct();
        $resultStruct->setResults(['item1', 'item2']);
        $resultStruct->setTotal(2);

        $searchCoordinatorMock = $this->createMock(SearchCoordinator::class);
        $searchCoordinatorMock->expects($this->once())
            ->method('search')
            ->with($keyword, $contextStub)
            ->willReturn($resultStruct);

        $subscriber = new PdfSearchSubscriber($searchCoordinatorMock);

        $event = new SuggestPageLoadedEvent($page, $salesChannelContextStub, $request);

        // Execute
        $subscriber->onSuggestPageLoaded($event);

        // Assertions
        $this->assertSame($resultStruct, $page->getExtension('pdfResults'));
        $this->assertSame(2, $page->getExtension('pdfResults')->getTotal());
    }
}