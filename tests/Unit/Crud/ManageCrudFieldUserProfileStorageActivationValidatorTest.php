<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Crud;

use App\Managing\Validator\Crud\ManageCrudFieldUserProfileStorageActivationValidator;
use PHPUnit\Framework\TestCase;

final class ManageCrudFieldUserProfileStorageActivationValidatorTest extends TestCase
{
    public function testDisabledDoctrineStoragePassesWithInformationalIssue(): void
    {
        $report = new ManageCrudFieldUserProfileStorageActivationValidator(
            runtimeBackend: 'config',
            readerBackend: 'none',
            writerBackend: 'none',
            entityManagerService: 'doctrine.orm.system_entity_manager',
        )->validate();

        self::assertTrue($report->passed);
        self::assertFalse($report->doctrineStorageEnabled);
        self::assertSame('field_user_profile_doctrine_storage_disabled', $report->issues[0]->code);
    }

    public function testDoctrineStorageRejectsDefaultEntityManagerService(): void
    {
        $report = new ManageCrudFieldUserProfileStorageActivationValidator(
            runtimeBackend: 'reader',
            readerBackend: 'doctrine',
            writerBackend: 'doctrine',
            entityManagerService: 'doctrine.orm.default_entity_manager',
        )->validate();

        self::assertFalse($report->passed);
        self::assertTrue($report->doctrineStorageEnabled);
        self::assertContains('field_user_profile_entity_manager_points_to_default', $this->issueCodes($report));
    }

    public function testRuntimeReaderRequiresReaderBackend(): void
    {
        $report = new ManageCrudFieldUserProfileStorageActivationValidator(
            runtimeBackend: 'reader',
            readerBackend: 'none',
            writerBackend: 'none',
            entityManagerService: 'doctrine.orm.system_entity_manager',
        )->validate();

        self::assertFalse($report->passed);
        self::assertContains('field_user_profile_runtime_reader_without_reader_backend', $this->issueCodes($report));
    }

    /** @return list<string> */
    private function issueCodes(object $report): array
    {
        return array_map(static fn (object $issue): string => $issue->code, $report->issues);
    }
}
