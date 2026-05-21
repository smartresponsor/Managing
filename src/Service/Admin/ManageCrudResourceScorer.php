<?php

declare(strict_types=1);

namespace App\Managing\Service\Admin;

use App\Managing\Service\Policy\ManagePolicyValueNormalizer;
use App\Managing\Value\ManageCrudResourceDefinition;

/**
 * Scores discovered CRUD resources so generators can pick the most business-facing entity per component.
 */
final readonly class ManageCrudResourceScorer
{
    /**
     * @param array<string, array<string, int>> $primaryEntityBonusSuffixesByComponent
     * @param array<string, array<string, int>> $primaryEntityPenaltySuffixesByComponent
     * @param list<string>                      $technicalKeywords
     * @param list<string>                      $businessKeywords
     */
    public function __construct(
        private array $primaryEntityBonusSuffixesByComponent = [],
        private array $primaryEntityPenaltySuffixesByComponent = [],
        private array $technicalKeywords = [],
        private array $businessKeywords = [],
        private ManagePolicyValueNormalizer $valueNormalizer = new ManagePolicyValueNormalizer(),
    ) {
    }

    public function score(
        ManageCrudResourceDefinition $resource,
        string $entityShortName,
        string $normalizedShortName,
        string $preferredRootName,
    ): int {
        $subject = strtolower($resource->resourceKey.' '.$resource->label.' '.$resource->entityClass);
        $score = 0;

        if ('' !== $preferredRootName) {
            if ($normalizedShortName === $preferredRootName) {
                $score += 40;
            } elseif (str_starts_with($normalizedShortName, $preferredRootName)) {
                $score += 20;
            } elseif (str_contains($normalizedShortName, $preferredRootName)) {
                $score += 10;
            }
        }

        foreach ($this->valueNormalizer->intMap($this->primaryEntityBonusSuffixesByComponent[$resource->componentKey] ?? []) as $suffix => $bonus) {
            if (str_ends_with($resource->entityClass, $suffix)) {
                $score += $bonus;
            }
        }

        foreach ($this->valueNormalizer->intMap($this->primaryEntityPenaltySuffixesByComponent[$resource->componentKey] ?? []) as $suffix => $penalty) {
            if (str_ends_with($resource->entityClass, $suffix)) {
                $score -= $penalty;
            }
        }

        foreach ($this->valueNormalizer->stringList($this->technicalKeywords) as $technicalKeyword) {
            if (str_contains($subject, $technicalKeyword)) {
                $score -= 20;
            }
        }

        foreach ($this->valueNormalizer->stringList($this->businessKeywords) as $businessKeyword) {
            if (str_contains($subject, $businessKeyword)) {
                $score += 10;
            }
        }

        $score -= intdiv(strlen($normalizedShortName), 4);

        if (str_ends_with($entityShortName, 'Entity')) {
            $score -= 5;
        }

        return $score;
    }
}
