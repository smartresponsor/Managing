<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Crud;

use App\Managing\Controller\Crud\ManageCrudControllerCustomizationTrait;
use PHPUnit\Framework\TestCase;

final class ManageCrudControllerCustomizationTraitTest extends TestCase
{
    public function testDefaultCustomizationHooksStayEmptyAndReadWrite(): void
    {
        self::assertNull(ManageCrudControllerCustomizationProbe::pluralLabel());
        self::assertNull(ManageCrudControllerCustomizationProbe::singularLabel());
        self::assertSame([], ManageCrudControllerCustomizationProbe::searchCandidates());
        self::assertSame([], ManageCrudControllerCustomizationProbe::arrayChoiceFields());
        self::assertSame([], ManageCrudControllerCustomizationProbe::fieldTypeOverrides());
        self::assertFalse(ManageCrudControllerCustomizationProbe::readOnly());
    }
}

final class ManageCrudControllerCustomizationProbe
{
    use ManageCrudControllerCustomizationTrait;

    public static function pluralLabel(): ?string
    {
        return self::manageContentPluralLabel();
    }

    public static function singularLabel(): ?string
    {
        return self::manageContentSingularLabel();
    }

    /** @return list<string> */
    public static function searchCandidates(): array
    {
        return self::manageSearchFieldCandidates();
    }

    /** @return array<string, array<string, string>> */
    public static function arrayChoiceFields(): array
    {
        return self::manageArrayChoiceFields();
    }

    /** @return array<string, array<string, string>|string> */
    public static function fieldTypeOverrides(): array
    {
        return self::manageFieldTypeOverrides();
    }

    public static function readOnly(): bool
    {
        return self::manageIsReadOnly();
    }
}
