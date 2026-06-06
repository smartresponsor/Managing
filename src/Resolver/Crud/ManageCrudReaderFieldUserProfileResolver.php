<?php

declare(strict_types=1);

namespace App\Managing\Resolver\Crud;

use App\Managing\ReaderInterface\Crud\ManageCrudFieldUserProfileReaderInterface;
use App\Managing\ResolverInterface\Crud\ManageCrudFieldUserProfileResolverInterface;
use App\Managing\Value\Crud\ManageCrudFieldDefinition;
use App\Managing\Value\Crud\ManageCrudFieldUserProfileDecision;
use App\Managing\Value\Crud\ManageCrudFieldUserProfileRuleSet;

/**
 * Reader-backed runtime resolver for personal Managing field view profiles.
 *
 * The resolver keeps profile storage behind the reader seam. It never grants access; it only
 * returns presentation preferences that the main visibility resolver may apply after access checks.
 */
final class ManageCrudReaderFieldUserProfileResolver implements ManageCrudFieldUserProfileResolverInterface
{
    /** @var array<string, ManageCrudFieldUserProfileRuleSet> */
    private array $ruleSetCache = [];

    public function __construct(private readonly ManageCrudFieldUserProfileReaderInterface $reader)
    {
    }

    public function decisionFor(ManageCrudFieldDefinition $definition, string $pageName, ?string $subjectIdentifier = null): ?ManageCrudFieldUserProfileDecision
    {
        $subjectIdentifier = $this->normalizeSubjectIdentifier($subjectIdentifier);
        if (null === $subjectIdentifier) {
            return null;
        }

        return $this->ruleSetFor($subjectIdentifier)->decisionFor($definition, $pageName, $subjectIdentifier);
    }

    private function ruleSetFor(string $subjectIdentifier): ManageCrudFieldUserProfileRuleSet
    {
        if (isset($this->ruleSetCache[$subjectIdentifier])) {
            return $this->ruleSetCache[$subjectIdentifier];
        }

        $readResult = $this->reader->read($subjectIdentifier);
        if (!$readResult->available) {
            return $this->ruleSetCache[$subjectIdentifier] = new ManageCrudFieldUserProfileRuleSet();
        }

        $profileConfig = $readResult->normalizedProfileConfig;
        if (!$this->containsWildcardSubject($profileConfig)) {
            $wildcardResult = $this->reader->read('*');
            if ($wildcardResult->available) {
                $profileConfig = $this->mergeProfileConfigs($wildcardResult->normalizedProfileConfig, $profileConfig);
            }
        }

        return $this->ruleSetCache[$subjectIdentifier] = ManageCrudFieldUserProfileRuleSet::fromArray($profileConfig);
    }

    private function normalizeSubjectIdentifier(?string $subjectIdentifier): ?string
    {
        if (null === $subjectIdentifier) {
            return null;
        }

        $subjectIdentifier = trim($subjectIdentifier);

        return '' === $subjectIdentifier ? null : $subjectIdentifier;
    }

    /** @param array<string, mixed> $profileConfig */
    private function containsWildcardSubject(array $profileConfig): bool
    {
        $subjects = $profileConfig['subjects'] ?? [];

        return is_array($subjects) && isset($subjects['*']);
    }

    /**
     * @param array<string, mixed> $baseConfig
     * @param array<string, mixed> $overlayConfig
     *
     * @return array<string, mixed>
     */
    private function mergeProfileConfigs(array $baseConfig, array $overlayConfig): array
    {
        $baseSubjects = $baseConfig['subjects'] ?? [];
        $overlaySubjects = $overlayConfig['subjects'] ?? [];

        if (!is_array($baseSubjects)) {
            $baseSubjects = [];
        }
        if (!is_array($overlaySubjects)) {
            $overlaySubjects = [];
        }

        return ['subjects' => array_replace_recursive($baseSubjects, $overlaySubjects)];
    }
}
