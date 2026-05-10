<?php

declare(strict_types=1);

namespace App\Managing\EventSubscriber;

use App\Managing\ServiceInterface\Security\ManageAdminAccessPolicyInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

final readonly class ManageAdminAccessSubscriber implements EventSubscriberInterface
{
    public function __construct(private ManageAdminAccessPolicyInterface $accessPolicy)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['guardManageAdminRequest', 64],
        ];
    }

    public function guardManageAdminRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $path = '/'.ltrim($event->getRequest()->getPathInfo(), '/');
        $prefix = $this->accessPolicy->getRoutePrefix();

        if ($path !== $prefix && !str_starts_with($path, $prefix.'/')) {
            return;
        }

        if (!$this->accessPolicy->isEnabled()) {
            throw new AccessDeniedHttpException('Managing admin is disabled by configuration.');
        }

        if (!$this->accessPolicy->isEnvironmentAllowed()) {
            throw new AccessDeniedHttpException('Managing admin is not enabled for the current environment.');
        }
    }
}
