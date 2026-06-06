<?php

declare(strict_types=1);

namespace App\Managing\Validator\Crud;

use App\Managing\Entity\Crud\ManageCrudFieldViewProfileRule;
use App\Managing\ValidatorInterface\Crud\ManageCrudFieldUserProfileStorageActivationValidatorInterface;
use App\Managing\Value\Crud\ManageCrudFieldUserProfileStorageActivationIssue as Issue;
use App\Managing\Value\Crud\ManageCrudFieldUserProfileStorageActivationReport;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Validates explicit host activation of Managing field view profile storage.
 */
final readonly class ManageCrudFieldUserProfileStorageActivationValidator implements ManageCrudFieldUserProfileStorageActivationValidatorInterface
{
    public function __construct(
        private string $runtimeBackend,
        private string $readerBackend,
        private string $writerBackend,
        private string $entityManagerService,
        private ?ManagerRegistry $managerRegistry = null,
    ) {
    }

    public function validate(): ManageCrudFieldUserProfileStorageActivationReport
    {
        $issues = [];
        $doctrineEnabled = $this->readerIsDoctrine() || $this->writerIsDoctrine();
        $this->validateBackendCombination($issues);

        if (!$doctrineEnabled) {
            $issues[] = Issue::info(
                'field_user_profile_doctrine_storage_disabled',
                'Doctrine profile storage is not enabled; no system EntityManager mapping check is required.',
            );

            return ManageCrudFieldUserProfileStorageActivationReport::fromIssues(false, $issues);
        }

        $this->validateEntityManagerService($issues);
        $this->validateDoctrineMapping($issues);

        return ManageCrudFieldUserProfileStorageActivationReport::fromIssues(true, $issues);
    }

    /** @param list<Issue> $issues */
    private function validateBackendCombination(array &$issues): void
    {
        if ('reader' === $this->runtimeBackend && 'none' === $this->readerBackend) {
            $issues[] = Issue::error(
                'field_user_profile_runtime_reader_without_reader_backend',
                'Runtime backend "reader" requires a configured profile reader backend.',
            );
        }

        if ($this->writerIsDoctrine() && !$this->readerIsDoctrine()) {
            $issues[] = Issue::warning(
                'field_user_profile_doctrine_writer_without_doctrine_reader',
                'Doctrine writer is enabled without Doctrine reader; merge operations will remain unavailable.',
            );
        }
    }

    /** @param list<Issue> $issues */
    private function validateEntityManagerService(array &$issues): void
    {
        $service = strtolower(trim($this->entityManagerService));
        if ('' === $service) {
            $issues[] = Issue::error(
                'field_user_profile_entity_manager_service_empty',
                'Doctrine profile storage requires managing.crud_field_user_profile_entity_manager_service.',
            );

            return;
        }

        if (in_array($service, ['doctrine.orm.entity_manager', 'doctrine.orm.default_entity_manager'], true)) {
            $issues[] = Issue::error(
                'field_user_profile_entity_manager_points_to_default',
                'Profile storage must not use the default/user-data EntityManager service.',
            );
        }

        if (!str_contains($service, 'system')) {
            $issues[] = Issue::error(
                'field_user_profile_entity_manager_not_system_scoped',
                'Profile storage EntityManager service must be explicitly system-scoped.',
            );
        }
    }

    /** @param list<Issue> $issues */
    private function validateDoctrineMapping(array &$issues): void
    {
        if (null === $this->managerRegistry) {
            $issues[] = Issue::warning(
                'field_user_profile_manager_registry_unavailable',
                'Doctrine manager registry is unavailable; mapping must be verified by the host application.',
            );

            return;
        }

        try {
            $manager = $this->managerRegistry->getManagerForClass(ManageCrudFieldViewProfileRule::class);
        } catch (\Throwable $exception) {
            $issues[] = Issue::error('field_user_profile_mapping_check_failed', $exception->getMessage());

            return;
        }

        if (null === $manager) {
            $issues[] = Issue::error(
                'field_user_profile_rule_mapping_missing',
                'ManageCrudFieldViewProfileRule is not mapped by a Doctrine EntityManager.',
            );

            return;
        }

        $issues[] = Issue::info(
            'field_user_profile_rule_mapping_found',
            'ManageCrudFieldViewProfileRule is mapped by Doctrine; verify the host generated the migration with --em=system.',
        );
    }

    private function readerIsDoctrine(): bool
    {
        return 'doctrine' === strtolower(trim($this->readerBackend));
    }

    private function writerIsDoctrine(): bool
    {
        return 'doctrine' === strtolower(trim($this->writerBackend));
    }
}
