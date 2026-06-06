<?php

declare(strict_types=1);

namespace App\Managing\Repository\Crud;

use App\Managing\Entity\Crud\ManageCrudFieldViewProfileRule;
use App\Managing\RepositoryInterface\Crud\ManageCrudFieldViewProfileRuleRepositoryInterface;
use App\Managing\Value\Crud\ManageCrudFieldUserProfileRuleSet;
use App\Managing\Value\Crud\ManageCrudFieldUserProfileWriteRequest;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Doctrine-backed storage adapter for Managing field view profile rules.
 *
 * Hosts should map this entity to their system/internal EntityManager, typically SQLite.
 */
final readonly class ManageDoctrineCrudFieldViewProfileRuleRepository implements ManageCrudFieldViewProfileRuleRepositoryInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function readProfileConfig(?string $subjectIdentifier = null): array
    {
        $criteria = [];
        $subjectIdentifier = $this->nullableString($subjectIdentifier);
        if (null !== $subjectIdentifier) {
            $criteria['subjectIdentifier'] = $subjectIdentifier;
        }

        /** @var list<ManageCrudFieldViewProfileRule> $rules */
        $rules = $this->entityManager->getRepository(ManageCrudFieldViewProfileRule::class)->findBy(
            $criteria,
            ['subjectIdentifier' => 'ASC', 'resourceKey' => 'ASC', 'pageName' => 'ASC'],
        );

        return $this->configFromRules($rules);
    }

    public function replacePageRule(ManageCrudFieldUserProfileWriteRequest $request): array
    {
        $subjectIdentifier = $request->normalizedSubjectIdentifier();
        $pageName = $request->normalizedPageName();
        if ('' === $subjectIdentifier || '' === $pageName) {
            return $this->readProfileConfig(null);
        }

        $resourceKey = ManageCrudFieldViewProfileRule::resourceKeyFromClass($request->normalizedResourceClass());
        $visibleFields = $request->normalizedVisibleFields();
        $hiddenFields = $request->normalizedHiddenFields();

        $rule = $this->findOneRule($subjectIdentifier, $resourceKey, $pageName);
        if ([] === $visibleFields && [] === $hiddenFields) {
            if (null !== $rule) {
                $this->entityManager->remove($rule);
                $this->entityManager->flush();
            }

            return $this->readProfileConfig($subjectIdentifier);
        }

        if (null === $rule) {
            $rule = new ManageCrudFieldViewProfileRule(
                $subjectIdentifier,
                $pageName,
                '*' === $resourceKey ? null : $resourceKey,
                $visibleFields,
                $hiddenFields,
                $request->actorIdentifier,
                $request->reason,
            );
            $this->entityManager->persist($rule);
        } else {
            $rule->replaceRule($visibleFields, $hiddenFields, $request->actorIdentifier, $request->reason);
        }

        $this->entityManager->flush();

        return $this->readProfileConfig($subjectIdentifier);
    }

    private function findOneRule(string $subjectIdentifier, string $resourceKey, string $pageName): ?ManageCrudFieldViewProfileRule
    {
        /** @var ManageCrudFieldViewProfileRule|null $rule */
        $rule = $this->entityManager->getRepository(ManageCrudFieldViewProfileRule::class)->findOneBy([
            'subjectIdentifier' => $subjectIdentifier,
            'resourceKey' => $resourceKey,
            'pageName' => $pageName,
        ]);

        return $rule;
    }

    /** @param list<ManageCrudFieldViewProfileRule> $rules @return array<string, mixed> */
    private function configFromRules(array $rules): array
    {
        $config = ['subjects' => []];
        foreach ($rules as $rule) {
            $subjectIdentifier = $rule->subjectIdentifier();
            $config['subjects'][$subjectIdentifier] ??= ['defaults' => [], 'resources' => []];
            $pageRule = [
                'visible' => $rule->visibleFields(),
                'hidden' => $rule->hiddenFields(),
            ];

            if ($rule->targetsResource()) {
                $resourceClass = $rule->resourceClass();
                if (null === $resourceClass) {
                    continue;
                }

                $config['subjects'][$subjectIdentifier]['resources'][$resourceClass][$rule->pageName()] = $pageRule;
            } else {
                $config['subjects'][$subjectIdentifier]['defaults'][$rule->pageName()] = $pageRule;
            }
        }

        return ManageCrudFieldUserProfileRuleSet::fromArray($config)->toArray();
    }

    private function nullableString(?string $value): ?string
    {
        if (null === $value) {
            return null;
        }

        $value = trim($value);

        return '' === $value ? null : $value;
    }
}
