<?php

declare(strict_types=1);

namespace App\Managing\Controller\Crud;

/**
 * Extension hooks for generated and hand-written Manage CRUD controllers.
 *
 * Keeping these hooks in a trait keeps AbstractManageContentCrudController
 * focused on EasyAdmin orchestration while preserving the existing override
 * contract for concrete component controllers.
 */
trait ManageCrudControllerCustomizationTrait
{
    /**
     * Override this in a concrete component CRUD controller when a domain needs
     * a custom plural label in the EasyAdmin header.
     */
    protected static function manageContentPluralLabel(): ?string
    {
        return null;
    }

    /**
     * Override this in a concrete component CRUD controller when a domain needs
     * a custom singular label in EasyAdmin breadcrumbs/actions.
     */
    protected static function manageContentSingularLabel(): ?string
    {
        return null;
    }

    /**
     * Override this in a concrete component CRUD controller to pin domain fields
     * that must be searched. When empty, ManageCrudBehaviorPolicy provides the
     * configured generic fallback.
     *
     * @return list<string>
     */
    protected static function manageSearchFieldCandidates(): array
    {
        return [];
    }

    /** @return list<string> */
    protected static function manageStatusFieldCandidates(): array
    {
        return [];
    }

    /** @return list<string> */
    protected static function managePublicationFlagCandidates(): array
    {
        return [];
    }

    /** @return list<string> */
    protected static function managePublicationDateCandidates(): array
    {
        return [];
    }

    /**
     * Override in a concrete CRUD controller when an array property should be
     * rendered as a choice field. This keeps component-specific vocabularies out
     * of the generic Manage controller layer.
     *
     * @return array<string, array<string, string>> keyed by entity field name
     */
    protected static function manageArrayChoiceFields(): array
    {
        return [];
    }

    /**
     * Override when a field must use a deterministic EasyAdmin field type rather
     * than the generic Doctrine/name fallback. Supported values: text, textarea,
     * email, url, boolean, integer, number, date, datetime and time.
     *
     * @return array<string, array<string, string>|string> keyed by field name or by entity FQCN then field name
     */
    protected static function manageFieldTypeOverrides(): array
    {
        return [];
    }

    protected static function manageIsReadOnly(): bool
    {
        return false;
    }
}
