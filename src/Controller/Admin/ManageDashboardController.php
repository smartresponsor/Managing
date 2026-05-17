<?php

declare(strict_types=1);

namespace App\Managing\Controller\Admin;

use App\Managing\ServiceInterface\Admin\ManageAdminRegistryInterface;
use App\Managing\ServiceInterface\Admin\ManageMenuBuilderInterface;
use App\Managing\Value\ManageCrudResourceDefinition;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\User\UserInterface;

#[AsController]
#[AdminDashboard(routePath: '/manage', routeName: 'manage')]
final class ManageDashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly ManageAdminRegistryInterface $adminRegistry,
        private readonly ManageMenuBuilderInterface $menuBuilder,
    ) {
    }

    public function index(): Response
    {
        return $this->render('manage/content.html.twig', [
            'page_title' => 'Manage',
            'content_title' => 'Business CRUD',
            'intro' => 'Native EasyAdmin business CRUD surface mounted under /manage.',
            'sections' => [
                [
                    'title' => 'Navigation',
                    'text' => 'Use the left menu to open business CRUD index pages.',
                ],
            ],
        ]);
    }

    #[AdminRoute(path: '/{componentKey}', name: 'component')]
    public function component(string $componentKey): Response
    {
        $resources = array_values(array_filter(
            $this->adminRegistry->getCrudResources(),
            static fn (ManageCrudResourceDefinition $resource): bool => $resource->componentKey === $componentKey,
        ));

        return $this->render('manage/content.html.twig', [
            'page_title' => ucfirst($componentKey).' index',
            'content_title' => ucfirst($componentKey).' index',
            'intro' => sprintf('Business records and resources for the %s component.', ucfirst($componentKey)),
            'sections' => [
                [
                    'table' => [
                        'headers' => ['Label', 'Resource', 'Mode'],
                        'rows' => array_map(
                            static function (ManageCrudResourceDefinition $resource): array {
                                return [
                                    $resource->label,
                                    $resource->resourceKey,
                                    $resource->mode,
                                ];
                            },
                            $resources,
                        ),
                        'emptyMessage' => 'No business resources are registered for this component.',
                    ],
                ],
            ],
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Managing')
            ->setFaviconPath('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"><text y="50" font-size="48">M</text></svg>');
    }

    public function configureMenuItems(): iterable
    {
        yield from $this->menuBuilder->buildMenuItems();
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        return UserMenu::new()
            ->setName($this->resolveUserName($user))
            ->displayUserName(true)
            ->displayUserAvatar(true);
    }

    private function resolveUserName(UserInterface $user): string
    {
        $identifier = trim($user->getUserIdentifier());

        return '' !== $identifier ? $identifier : 'Managing user';
    }
}
