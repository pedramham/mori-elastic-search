<?php

namespace MoriElasticSearch\Subscriber;

use MoriElasticSearch\Service\Search\SearchCoordinator;
use Shopware\Storefront\Page\Suggest\SuggestPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PdfSearchSubscriber implements EventSubscriberInterface
{
    private SearchCoordinator $searchCoordinator;

    public function __construct(SearchCoordinator $searchCoordinator)
    {
        $this->searchCoordinator = $searchCoordinator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SuggestPageLoadedEvent::class => 'onSuggestPageLoaded'
        ];
    }

    public function onSuggestPageLoaded(SuggestPageLoadedEvent $event): void
    {
        $keyword = $event->getRequest()->query->get('search');
        if (!$keyword) {
            return;
        }

        $resultStruct = $this->searchCoordinator->search($keyword, $event->getContext());
        $event->getPage()->addExtension('pdfResults', $resultStruct);

    }
}