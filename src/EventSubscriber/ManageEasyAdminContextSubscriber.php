<?php

declare(strict_types=1);

namespace App\Managing\EventSubscriber;

use App\Managing\Controller\Admin\ManageDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Factory\AdminContextFactory;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminRouteGenerator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final readonly class ManageEasyAdminContextSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private AdminContextFactory $adminContextFactory,
        private AdminRouteGenerator $adminRouteGenerator,
        private ManageDashboardController $dashboardController,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 40],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        if (!str_starts_with($request->getPathInfo(), '/manage')) {
            return;
        }

        if ($request->attributes->has(EA::CONTEXT_REQUEST_ATTRIBUTE)) {
            return;
        }

        if ([] === $this->adminRouteGenerator->getDashboardRoutes()) {
            $this->adminRouteGenerator->generateAll();
        }

        $request->attributes->set(
            EA::CONTEXT_REQUEST_ATTRIBUTE,
            $this->adminContextFactory->create($request, $this->dashboardController, null),
        );
    }
}
