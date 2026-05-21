<?php

declare(strict_types=1);

namespace App\Managing\DependencyInjection;

/**
 * Default menu configuration values for the Managing bundle.
 */
final class ManagingMenuConfigurationDefaults
{
    /** @return list<string> */
    public static function leftMenu(): array
    {
        return [
            'analysing',
            'applicating',
            'attaching',
            'billing',
            'cataloging',
            'commissioning',
            'currencing',
            'exchanging',
            'localizing',
            'messaging',
            'ordering',
            'paging',
            'paying',
            'rolling',
            'shipping',
            'subscripting',
            'tagging',
            'taxating',
            'vendoring',
        ];
    }

    /** @return list<string> */
    public static function excludedComponents(): array
    {
        return ['managing', 'cruding', 'interfacing'];
    }
}
