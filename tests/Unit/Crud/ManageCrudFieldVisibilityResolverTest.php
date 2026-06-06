<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Crud;

use App\Managing\Resolver\Crud\ManageCrudFieldVisibilityResolver;
use App\Managing\ResolverInterface\Crud\ManageCrudFieldExternalAccessResolverInterface;
use App\Managing\ResolverInterface\Crud\ManageCrudFieldUserProfileResolverInterface;
use App\Managing\Value\Crud\ManageCrudFieldAccessContext;
use App\Managing\Value\Crud\ManageCrudFieldDefinition;
use App\Managing\Value\Crud\ManageCrudFieldExternalAccessDecision;
use App\Managing\Value\Crud\ManageCrudFieldUserProfileDecision;
use PHPUnit\Framework\TestCase;

final class ManageCrudFieldVisibilityResolverTest extends TestCase
{
    public function testUnavailableFieldIsNotRenderable(): void
    {
        $decision = (new ManageCrudFieldVisibilityResolver())->decisionFor(
            new ManageCrudFieldDefinition('Managing', self::class, 'secret', 'Secret', availableOn: ['detail']),
            'index',
        );

        self::assertFalse($decision->renderable());
        self::assertSame('field_not_available_on_page', $decision->reason);
    }

    public function testDefaultHiddenFieldKeepsAccessButIsNotVisible(): void
    {
        $decision = (new ManageCrudFieldVisibilityResolver())->decisionFor(
            new ManageCrudFieldDefinition('Managing', self::class, 'notes', 'Notes', defaultVisible: false),
            'index',
        );

        self::assertTrue($decision->accessAllowed);
        self::assertFalse($decision->visible);
        self::assertFalse($decision->renderable());
    }

    public function testBackendConfigCanHideAllowedField(): void
    {
        $decision = (new ManageCrudFieldVisibilityResolver([
            'defaults' => [
                'index' => ['hidden' => ['createdAt']],
            ],
        ]))->decisionFor(
            new ManageCrudFieldDefinition('Managing', self::class, 'createdAt', 'Created At'),
            'index',
        );

        self::assertTrue($decision->accessAllowed);
        self::assertFalse($decision->visible);
        self::assertSame('default_configured_hidden', $decision->reason);
    }

    public function testBackendConfigCanDenyFieldBeforeRendering(): void
    {
        $decision = (new ManageCrudFieldVisibilityResolver([
            'resources' => [
                self::class => [
                    'detail' => ['denied' => ['internalCost']],
                ],
            ],
        ]))->decisionFor(
            new ManageCrudFieldDefinition('Managing', self::class, 'internalCost', 'Internal Cost', availableOn: ['detail']),
            'detail',
        );

        self::assertFalse($decision->accessAllowed);
        self::assertFalse($decision->visible);
        self::assertSame('resource_configured_denied', $decision->reason);
    }

    public function testResourceConfigOverridesDefaultVisibilityConfig(): void
    {
        $decision = (new ManageCrudFieldVisibilityResolver([
            'defaults' => [
                'all' => ['hidden' => ['status']],
            ],
            'resources' => [
                self::class => [
                    'index' => ['visible' => ['status']],
                ],
            ],
        ]))->decisionFor(
            new ManageCrudFieldDefinition('Managing', self::class, 'status', 'Status'),
            'index',
        );

        self::assertTrue($decision->renderable());
        self::assertSame('resource_configured_visible', $decision->reason);
    }

    public function testUserProfileCanShowBackendHiddenAllowedField(): void
    {
        $resolver = new ManageCrudFieldVisibilityResolver(
            fieldVisibilityConfig: [
                'defaults' => [
                    'index' => ['hidden' => ['createdAt']],
                ],
            ],
            userProfileResolver: new class implements ManageCrudFieldUserProfileResolverInterface {
                public function decisionFor(ManageCrudFieldDefinition $definition, string $pageName, ?string $subjectIdentifier = null): ?ManageCrudFieldUserProfileDecision
                {
                    return ManageCrudFieldUserProfileDecision::visible($definition->fieldName);
                }
            },
        );

        $decision = $resolver->decisionFor(
            new ManageCrudFieldDefinition('Managing', self::class, 'createdAt', 'Created At'),
            'index',
            'user:42',
        );

        self::assertTrue($decision->renderable());
        self::assertSame('user_profile', $decision->source);
        self::assertSame('user_profile_visible', $decision->reason);
    }

    public function testUserProfileCannotOverrideConfiguredDeniedField(): void
    {
        $resolver = new ManageCrudFieldVisibilityResolver(
            fieldVisibilityConfig: [
                'defaults' => [
                    'detail' => ['denied' => ['internalCost']],
                ],
            ],
            userProfileResolver: new class implements ManageCrudFieldUserProfileResolverInterface {
                public function decisionFor(ManageCrudFieldDefinition $definition, string $pageName, ?string $subjectIdentifier = null): ?ManageCrudFieldUserProfileDecision
                {
                    return ManageCrudFieldUserProfileDecision::visible($definition->fieldName);
                }
            },
        );

        $decision = $resolver->decisionFor(
            new ManageCrudFieldDefinition('Managing', self::class, 'internalCost', 'Internal Cost'),
            'detail',
            'user:42',
        );

        self::assertFalse($decision->accessAllowed);
        self::assertFalse($decision->visible);
        self::assertSame('default_configured_denied', $decision->reason);
    }

    public function testUserProfileCannotHideRequiredFormField(): void
    {
        $resolver = new ManageCrudFieldVisibilityResolver(
            userProfileResolver: new class implements ManageCrudFieldUserProfileResolverInterface {
                public function decisionFor(ManageCrudFieldDefinition $definition, string $pageName, ?string $subjectIdentifier = null): ?ManageCrudFieldUserProfileDecision
                {
                    return ManageCrudFieldUserProfileDecision::hidden($definition->fieldName);
                }
            },
        );

        $decision = $resolver->decisionFor(
            new ManageCrudFieldDefinition('Managing', self::class, 'title', 'Title', requiredOnForm: true),
            'new',
            'user:42',
        );

        self::assertTrue($decision->renderable());
        self::assertSame('field_default_visible', $decision->reason);
    }

    public function testExternalAccessDenyWinsBeforePresentationVisibility(): void
    {
        $resolver = new ManageCrudFieldVisibilityResolver(
            fieldVisibilityConfig: [
                'resources' => [
                    self::class => [
                        'index' => ['visible' => ['internalCost']],
                    ],
                ],
            ],
            externalAccessResolver: new class implements ManageCrudFieldExternalAccessResolverInterface {
                public function decisionFor(ManageCrudFieldAccessContext $context): ManageCrudFieldExternalAccessDecision
                {
                    return ManageCrudFieldExternalAccessDecision::deny(
                        ManageCrudFieldExternalAccessDecision::SOURCE_ROLLING,
                        'rolling_field_access_denied',
                    );
                }
            },
        );

        $decision = $resolver->decisionFor(
            new ManageCrudFieldDefinition('Managing', self::class, 'internalCost', 'Internal Cost'),
            'index',
            'user:42',
        );

        self::assertFalse($decision->accessAllowed);
        self::assertFalse($decision->visible);
        self::assertSame('rolling', $decision->source);
        self::assertSame('rolling_field_access_denied', $decision->reason);
    }

    public function testExternalAccessAllowDoesNotForceDefaultHiddenFieldVisible(): void
    {
        $resolver = new ManageCrudFieldVisibilityResolver(
            externalAccessResolver: new class implements ManageCrudFieldExternalAccessResolverInterface {
                public function decisionFor(ManageCrudFieldAccessContext $context): ManageCrudFieldExternalAccessDecision
                {
                    return ManageCrudFieldExternalAccessDecision::allow(
                        ManageCrudFieldExternalAccessDecision::SOURCE_ROLLING,
                        'rolling_field_access_allowed',
                    );
                }
            },
        );

        $decision = $resolver->decisionFor(
            new ManageCrudFieldDefinition('Managing', self::class, 'notes', 'Notes', defaultVisible: false),
            'index',
            'user:42',
        );

        self::assertTrue($decision->accessAllowed);
        self::assertFalse($decision->visible);
        self::assertSame('field_default_hidden', $decision->reason);
    }
}
