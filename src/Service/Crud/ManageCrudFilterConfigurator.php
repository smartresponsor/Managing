<?php

declare(strict_types=1);

namespace App\Managing\Service\Crud;

use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;

/**
 * Builds the shared EasyAdmin filter surface for generated Manage CRUD screens.
 */
final class ManageCrudFilterConfigurator
{
    /**
     * @param list<string> $statusFields
     * @param list<string> $publicationFlagFields
     * @param list<string> $dateFields
     */
    public function configure(Filters $filters, array $statusFields, array $publicationFlagFields, array $dateFields): Filters
    {
        foreach ($statusFields as $field) {
            $filters->add(TextFilter::new($field));
        }

        foreach ($publicationFlagFields as $field) {
            $filters->add(BooleanFilter::new($field));
        }

        foreach ($dateFields as $field) {
            $filters->add(DateTimeFilter::new($field));
        }

        return $filters;
    }
}
