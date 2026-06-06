<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit;

use App\Managing\Value\Crud\ManageCrudFieldAccessContext;
use App\Managing\Value\Crud\ManageCrudFieldDefinition;
use App\Managing\Value\Crud\ManageCrudFieldExternalAccessDecision;
use App\Managing\Value\Crud\ManageCrudFieldPermissionVocabulary;
use App\Managing\Value\Crud\ManageCrudFieldVisibilityDecision;
use PHPUnit\Framework\TestCase;

final class ManageCrudFieldVisibilityVocabularyTest extends TestCase
{
    public function testPermissionVocabularyContainsCanonicalFieldAccessKeys(): void
    {
        self::assertContains('managing.field.view', ManageCrudFieldPermissionVocabulary::all());
        self::assertContains('managing.field.profile.role_update', ManageCrudFieldPermissionVocabulary::all());
        self::assertContains('managing.field.profile.group_update', ManageCrudFieldPermissionVocabulary::all());
    }

    public function testFieldDefinitionIsNeutralBeforeEasyAdminFieldCreation(): void
    {
        $definition = new ManageCrudFieldDefinition(
            componentKey: 'Managing',
            resourceClass: 'App\\Cataloging\\Entity\\Catalog\\CatalogCategoryEntity',
            fieldName: 'internalCost',
            label: 'Internal Cost',
            fieldType: 'money',
            availableOn: ['detail'],
            sensitive: true,
            defaultVisible: false,
        );

        self::assertTrue($definition->isAvailableOn('detail'));
        self::assertFalse($definition->isAvailableOn('index'));
        self::assertSame('managing.field.view', $definition->permissionKey);
        self::assertTrue($definition->toArray()['sensitive']);
    }

    public function testVisibilityDecisionRequiresAccessAndVisibilityBeforeRendering(): void
    {
        $allowedButHidden = new ManageCrudFieldVisibilityDecision('createdAt', true, false, ManageCrudFieldVisibilityDecision::SOURCE_USER_PROFILE);
        $deniedButRequested = new ManageCrudFieldVisibilityDecision('internalCost', false, true, ManageCrudFieldVisibilityDecision::SOURCE_ROLLING);
        $allowedAndVisible = new ManageCrudFieldVisibilityDecision('title', true, true);

        self::assertFalse($allowedButHidden->renderable());
        self::assertFalse($deniedButRequested->renderable());
        self::assertTrue($allowedAndVisible->renderable());
    }

    public function testAccessContextExportsRollingFriendlyAttributes(): void
    {
        $context = new ManageCrudFieldAccessContext(
            componentKey: 'Managing',
            resourceClass: 'App\\Cataloging\\Entity\\Catalog\\CatalogCategoryEntity',
            fieldName: 'firstTitle',
            pageName: ManageCrudFieldAccessContext::PAGE_INDEX,
            subjectIdentifier: 'user:42',
        );

        self::assertSame([
            'component' => 'Managing',
            'resource' => 'App\\Cataloging\\Entity\\Catalog\\CatalogCategoryEntity',
            'field' => 'firstTitle',
            'page' => 'index',
            'operation' => 'view',
            'subject' => 'user:42',
        ], $context->toDecisionAttributes());
    }

    public function testExternalAccessDecisionSeparatesAccessFromPresentation(): void
    {
        $allow = ManageCrudFieldExternalAccessDecision::allow(
            ManageCrudFieldExternalAccessDecision::SOURCE_ROLLING,
            'matched_role_permission',
        );
        $deny = ManageCrudFieldExternalAccessDecision::deny(
            ManageCrudFieldExternalAccessDecision::SOURCE_ROLLING,
            'matched_direct_deny',
        );
        $abstain = ManageCrudFieldExternalAccessDecision::abstain(reason: 'no_external_provider');

        self::assertTrue($allow->allows());
        self::assertFalse($allow->denies());
        self::assertTrue($deny->denies());
        self::assertTrue($abstain->abstains());
    }
}
