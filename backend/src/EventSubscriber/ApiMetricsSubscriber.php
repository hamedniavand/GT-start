<?php

namespace App\EventSubscriber;

use App\Doctrine\QueryCounter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class ApiMetricsSubscriber implements EventSubscriberInterface
{
    private ?float $startedAt = null;

    public function __construct(private readonly QueryCounter $counter)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 4096],
            KernelEvents::RESPONSE => ['onResponse', -4096],
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        if ($event->isMainRequest()) {
            $this->startedAt = hrtime(true);
        }
    }

    public function onResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest() || !str_starts_with($event->getRequest()->getPathInfo(), '/api')) {
            return;
        }

        $headers = $event->getResponse()->headers;
        $headers->set('X-Query-Count', (string) $this->counter->count());

        if (null !== $this->startedAt) {
            $headers->set('X-Response-Time-Ms', (string) (int) round((hrtime(true) - $this->startedAt) / 1e6));
        }
    }
}
