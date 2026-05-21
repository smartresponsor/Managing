<?php

declare(strict_types=1);

namespace App\Managing\Service\Admin;

/**
 * File-backed marker for one-off attachment identifier migration.
 *
 * Keeping marker I/O outside the migration service keeps the migration
 * orchestration focused on database state transitions and makes the safety
 * gate explicit for tests and future host-application overrides.
 */
final readonly class AttachmentIdentifierMigrationMarker
{
    public function __construct(
        private ?string $cacheMarkerFile = null,
    ) {
    }

    public function isComplete(): bool
    {
        return null !== $this->cacheMarkerFile && is_file($this->cacheMarkerFile);
    }

    public function markComplete(): void
    {
        if (null === $this->cacheMarkerFile) {
            return;
        }

        $directory = dirname($this->cacheMarkerFile);
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        file_put_contents($this->cacheMarkerFile, '1', LOCK_EX);
    }
}
