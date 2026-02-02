<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class AddPaginationMetadataSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => ['addPaginationMetadata', 10],
        ];
    }

    public function addPaginationMetadata(ResponseEvent $event): void
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        // Only process API Platform responses
        if (!str_starts_with($request->getPathInfo(), '/api/') || $response->headers->get('content-type') !== 'application/ld+json; charset=utf-8') {
            return;
        }

        $content = $response->getContent();
        if (!$content) {
            return;
        }

        $data = json_decode($content, true);
        if (!$data || !isset($data['totalItems'])) {
            return;
        }

        // Get pagination parameters
        $page = (int) $request->query->get('page', 1);
        $itemsPerPage = (int) $request->query->get('itemsPerPage', 30);
        $totalItems = $data['totalItems'];
        $totalPages = (int) ceil($totalItems / $itemsPerPage);

        // Add pagination metadata
        $data['pagination'] = [
            'page' => $page,
            'itemsPerPage' => $itemsPerPage,
            'totalPages' => $totalPages,
            'totalItems' => $totalItems,
            'hasNextPage' => $page < $totalPages,
            'hasPreviousPage' => $page > 1,
        ];

        $response->setContent(json_encode($data));
    }
}
