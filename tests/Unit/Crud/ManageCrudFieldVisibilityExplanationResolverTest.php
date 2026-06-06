<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Crud;

use App\Managing\Resolver\Crud\ManageCrudFieldVisibilityExplanationResolver;
use App\Managing\ResolverInterface\Crud\ManageCrudFieldExternalAccessResolverInterface;
use App\Managing\ResolverInterface\Crud\ManageCrudFieldUserProfileResolverInterface;
use App\Managing\Value\Crud\ManageCrudFieldAccessContext;
use App\Managing\Value\Crud\ManageCrudFieldDefinition;
use App\Managing\Value\Crud\ManageCrudFieldExternalAccessDecision;
use App\Managing\Value\Crud\ManageCrudFieldUserProfileDecision;
use App\Managing\Value\Crud\ManageCrudFieldVisibilityExplanationStep;
use PHPUnit\Framework\TestCase;

final class ManageCrudFieldVisibilityExplanationResolverTest extends TestCase
{
    public function testExplainsUnavailableFieldAsTerminalSystemDeny(): void
    {
        $explanation = (new ManageCrudFieldVisibilityExplanationResolver())->explainFor(
            new ManageCrudFieldDefinition('Managing', self::class, 'secret', 'Secret', availableOn: ['detail']),
            'index',
        );

        self::assertFalse($explanation->renderable());
        self::assertTrue($explanation->denied());
        self::assertSame('field_not_available_on_page', $explanation->finalDecision->reason);
        self::assertSame('deny', $explanation->steps[0]->effect);
        self::assertSame(ManageCrudFieldVisibilityExplanationStep::AXIS_AVAILABILITY, $explanation->steps[0]->axis);
        self::assertTrue($explanation->steps[0]->terminal);
    }

    public function testExplainsBackendHiddenThenUserProfileVisible(): void
    {
        $resolver = new ManageCrudFieldVisibilityExplanationResolver(
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

        $explanation = $resolver->explainFor(
            new ManageCrudFieldDefinition('Managing', self::class, 'createdAt', 'Created At'),
            'index',
            'user:42',
        );

        self::assertTrue($explanation->renderable());
        self::assertSame('user_profile_visible', $explanation->finalDecision->reason);
        self::assertSame(ManageCrudFieldVisibilityExplanationStep::AXIS_PRESENTATION, $explanation->finalAxis());
        self::assertContains('default_configured_hidden', $explanation->reasons());
        self::assertContains('user_profile_visible', $explanation->reasons());
    }

    public function testExplainsRollingDenyAsTerminalBeforeUserProfile(): void
    {
        $resolver = new ManageCrudFieldVisibilityExplanationResolver(
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
            userProfileResolver: new class implements ManageCrudFieldUserProfileResolverInterface {
                public function decisionFor(ManageCrudFieldDefinition $definition, string $pageName, ?string $subjectIdentifier = null): ?ManageCrudFieldUserProfileDecision
                {
                    return ManageCrudFieldUserProfileDecision::visible($definition->fieldName);
                }
            },
        );

        $explanation = $resolver->explainFor(
            new ManageCrudFieldDefinition('Managing', self::class, 'internalCost', 'Internal Cost'),
            'index',
            'user:42',
        );

        self::assertFalse($explanation->renderable());
        self::assertSame('rolling', $explanation->finalDecision->source);
        self::assertSame('rolling_field_value_access_denied', $explanation->finalDecision->reason);
        self::assertSame(ManageCrudFieldVisibilityExplanationStep::AXIS_ACCESS, $explanation->finalAxis());
        self::assertTrue($explanation->accessDenied());
        self::assertTrue($explanation->steps[1]->terminal);
        self::assertContains('resource_configured_visible', $explanation->reasons());
        self::assertContains('rolling_field_value_access_denied', $explanation->reasons());
        self::assertSame('rolling_field_access_denied', $explanation->steps[1]->context['providerReason'] ?? null);
    }

    public function testExplainsIgnoredUserProfileHideForRequiredFormField(): void
    {
        $resolver = new ManageCrudFieldVisibilityExplanationResolver(
            userProfileResolver: new class implements ManageCrudFieldUserProfileResolverInterface {
                public function decisionFor(ManageCrudFieldDefinition $definition, string $pageName, ?string $subjectIdentifier = null): ?ManageCrudFieldUserProfileDecision
                {
                    return ManageCrudFieldUserProfileDecision::hidden($definition->fieldName);
                }
            },
        );

        $explanation = $resolver->explainFor(
            new ManageCrudFieldDefinition('Managing', self::class, 'title', 'Title', requiredOnForm: true),
            'edit',
            'user:42',
        );

        self::assertTrue($explanation->renderable());
        self::assertSame('field_default_visible', $explanation->finalDecision->reason);
        self::assertContains('user_profile_hide_not_allowed', $explanation->reasons());
        self::assertSame('ignored', $explanation->steps[array_key_last($explanation->steps)]->effect);
        self::assertFalse($explanation->presentationHidden());
    }
}
