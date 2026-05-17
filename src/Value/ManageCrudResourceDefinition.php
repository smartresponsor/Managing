<?php

declare(strict_types=1);

namespace App\Managing\Value;

final readonly class ManageCrudResourceDefinition
{
    public const MODE_EASYADMIN = 'easyadmin';
    public const MODE_CRUDING_LINK = 'cruding_link';
    public const MODE_CUSTOM_ROUTE = 'custom_route';

    public const SURFACE_MANAGE = 'manage';
    public const SURFACE_SYSTEM = 'system';

    public function __construct(
        public string $componentKey,
        public string $resourceKey,
        public string $label,
        public string $entityClass,
        public ?string $crudControllerClass = null,
        public ?string $formTypeClass = null,
        public ?string $routeNamePattern = null,
        public ?string $menuGroup = null,
        public bool $enabled = true,
        public string $mode = self::MODE_EASYADMIN,
        public ?string $resourcePath = null,
        public string $identifierField = 'id',
        public string $surface = self::SURFACE_MANAGE,
        public string $templatePrefix = 'crud',
    ) {
    }
}
