<?php

declare(strict_types=1);

namespace App\Managing\Writer\Crud;

use App\Managing\Value\Crud\ManageCrudFieldUserProfileRuleSet;
use App\Managing\Value\Crud\ManageCrudFieldUserProfileWriteRequest;
use App\Managing\Value\Crud\ManageCrudFieldUserProfileWriteResult;
use App\Managing\WriterInterface\Crud\ManageCrudFieldUserProfileWriterInterface;

/**
 * Builds the normalized config-shaped payload for a personal field view profile update.
 *
 * This writer deliberately does not write files or Doctrine rows. It is the persistence seam
 * used by future UI/storage implementations to validate and prepare the exact payload shape.
 */
final class ManageCrudConfigShapeFieldUserProfileWriter implements ManageCrudFieldUserProfileWriterInterface
{
    /** @var array<string, mixed> */
    private array $profileConfig;

    /** @param array<string, mixed> $fieldUserProfilesConfig */
    public function __construct(array $fieldUserProfilesConfig = [])
    {
        $this->profileConfig = ManageCrudFieldUserProfileRuleSet::fromArray($fieldUserProfilesConfig)->toArray();
    }

    public function write(ManageCrudFieldUserProfileWriteRequest $request): ManageCrudFieldUserProfileWriteResult
    {
        $subjectIdentifier = $request->normalizedSubjectIdentifier();
        if ('' === $subjectIdentifier) {
            return ManageCrudFieldUserProfileWriteResult::rejected('field_user_profile_subject_required');
        }

        $pageName = $request->normalizedPageName();
        if ('' === $pageName) {
            return ManageCrudFieldUserProfileWriteResult::rejected('field_user_profile_page_required');
        }

        $visibleFields = $request->normalizedVisibleFields();
        $hiddenFields = $request->normalizedHiddenFields();
        $overlap = array_values(array_intersect($visibleFields, $hiddenFields));
        if ([] !== $overlap) {
            return ManageCrudFieldUserProfileWriteResult::rejected('field_user_profile_conflicting_field_preferences');
        }

        $config = $this->profileConfig;
        $config['subjects'] ??= [];
        $config['subjects'][$subjectIdentifier] ??= ['defaults' => [], 'resources' => []];

        if ($request->targetsResource()) {
            $resourceClass = $request->normalizedResourceClass();
            if (null === $resourceClass) {
                return ManageCrudFieldUserProfileWriteResult::rejected('field_user_profile_resource_required');
            }

            $config['subjects'][$subjectIdentifier]['resources'][$resourceClass] ??= [];
            $config['subjects'][$subjectIdentifier]['resources'][$resourceClass] = $this->replacePageRule(
                $config['subjects'][$subjectIdentifier]['resources'][$resourceClass],
                $pageName,
                $visibleFields,
                $hiddenFields,
            );

            $config = $this->pruneEmptyResource($config, $subjectIdentifier, $resourceClass);
        } else {
            $config['subjects'][$subjectIdentifier]['defaults'] = $this->replacePageRule(
                $config['subjects'][$subjectIdentifier]['defaults'],
                $pageName,
                $visibleFields,
                $hiddenFields,
            );
        }

        $config = $this->pruneEmptySubject($config, $subjectIdentifier);
        $this->profileConfig = $config;

        return ManageCrudFieldUserProfileWriteResult::accepted($config);
    }

    /**
     * @param array<string, array<string, list<string>>> $pageRules
     * @param list<string>                               $visibleFields
     * @param list<string>                               $hiddenFields
     *
     * @return array<string, array<string, list<string>>>
     */
    private function replacePageRule(array $pageRules, string $pageName, array $visibleFields, array $hiddenFields): array
    {
        if ([] === $visibleFields && [] === $hiddenFields) {
            unset($pageRules[$pageName]);

            return $pageRules;
        }

        $pageRules[$pageName] = [
            'visible' => $visibleFields,
            'hidden' => $hiddenFields,
        ];

        return $pageRules;
    }

    /** @param array<string, mixed> $config @return array<string, mixed> */
    private function pruneEmptyResource(array $config, string $subjectIdentifier, string $resourceClass): array
    {
        if ([] === ($config['subjects'][$subjectIdentifier]['resources'][$resourceClass] ?? [])) {
            unset($config['subjects'][$subjectIdentifier]['resources'][$resourceClass]);
        }

        return $config;
    }

    /** @param array<string, mixed> $config @return array<string, mixed> */
    private function pruneEmptySubject(array $config, string $subjectIdentifier): array
    {
        $subject = $config['subjects'][$subjectIdentifier] ?? null;
        if (!is_array($subject)) {
            return $config;
        }

        if ([] === ($subject['defaults'] ?? []) && [] === ($subject['resources'] ?? [])) {
            unset($config['subjects'][$subjectIdentifier]);
        }

        return $config;
    }
}
