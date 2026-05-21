<?php

declare(strict_types=1);

namespace App\Managing\Service\Admin;

use App\Managing\Service\Policy\ManagePolicyValueNormalizer;
use App\Managing\Value\ManageCrudResourceDefinition;

/**
 * Keeps component/resource selection rules outside generic host discovery and controller generation.
 *
 * Managing may provide sensible defaults for known sibling components, but the discovery and
 * generation services must consume these defaults as policy data rather than hard-coded branches.
 */
final class ManageCrudResourcePolicy
{
    /** @var array<string, string> */
    private readonly array $componentRootNames;

    /** @var array<string, string> */
    private readonly array $componentRootAliases;

    /**
     * @param array<string, string>             $componentRootNames
     * @param array<string, string>             $componentRootAliases
     * @param array<string, list<string>>       $includedEntitySuffixesByComponent
     * @param array<string, array<string, int>> $primaryEntityBonusSuffixesByComponent
     * @param array<string, array<string, int>> $primaryEntityPenaltySuffixesByComponent
     * @param list<string>                      $technicalKeywords
     * @param list<string>                      $businessKeywords
     * @param list<string>                      $componentsRequiringAttachmentIdentifierMigration
     */
    public function __construct(
        array $componentRootNames = [],
        array $componentRootAliases = [],
        private readonly array $includedEntitySuffixesByComponent = [],
        private readonly array $primaryEntityBonusSuffixesByComponent = [],
        private readonly array $primaryEntityPenaltySuffixesByComponent = [],
        private readonly array $technicalKeywords = [],
        private readonly array $businessKeywords = [],
        private readonly array $componentsRequiringAttachmentIdentifierMigration = [],
        private readonly ManagePolicyValueNormalizer $valueNormalizer = new ManagePolicyValueNormalizer(),
        private readonly ?ManageCrudResourceScorer $resourceScorer = null,
    ) {
        $this->componentRootNames = $this->valueNormalizer->normalizedStringMap($componentRootNames);
        $this->componentRootAliases = $this->valueNormalizer->normalizedStringMap($componentRootAliases);
    }

    public function shouldIncludeDiscoveredEntity(string $componentKey, string $className): bool
    {
        $suffixes = $this->valueNormalizer->stringList($this->includedEntitySuffixesByComponent[$componentKey] ?? []);
        if ([] === $suffixes) {
            return true;
        }

        foreach ($suffixes as $suffix) {
            if (str_ends_with($className, $suffix)) {
                return true;
            }
        }

        return false;
    }

    public function componentKeyFromRootName(string $rootName): ?string
    {
        $normalizedRootName = strtolower(trim($rootName));
        if ('' === $normalizedRootName) {
            return null;
        }

        if (isset($this->componentRootAliases[$normalizedRootName])) {
            return $this->componentRootAliases[$normalizedRootName];
        }

        foreach ($this->componentRootNames as $componentKey => $configuredRootName) {
            if (!is_string($componentKey) || !is_string($configuredRootName)) {
                continue;
            }

            if ($normalizedRootName === strtolower(trim($configuredRootName))) {
                return strtolower(trim($componentKey));
            }
        }

        return null;
    }

    public function preferredRootName(string $componentKey): string
    {
        $rootName = $this->componentRootNames[$componentKey] ?? $componentKey;
        if (!is_string($rootName)) {
            return strtolower($componentKey);
        }

        return strtolower(trim($rootName));
    }

    public function requiresAttachmentIdentifierMigration(string $componentKey): bool
    {
        return in_array($componentKey, $this->valueNormalizer->stringList($this->componentsRequiringAttachmentIdentifierMigration), true);
    }

    public function scoreResource(ManageCrudResourceDefinition $resource, string $entityShortName, string $normalizedShortName): int
    {
        return $this->resourceScorer()->score(
            $resource,
            $entityShortName,
            $normalizedShortName,
            $this->preferredRootName($resource->componentKey),
        );
    }

    private function resourceScorer(): ManageCrudResourceScorer
    {
        return $this->resourceScorer ?? new ManageCrudResourceScorer(
            primaryEntityBonusSuffixesByComponent: $this->primaryEntityBonusSuffixesByComponent,
            primaryEntityPenaltySuffixesByComponent: $this->primaryEntityPenaltySuffixesByComponent,
            technicalKeywords: $this->technicalKeywords,
            businessKeywords: $this->businessKeywords,
            valueNormalizer: $this->valueNormalizer,
        );
    }
}
