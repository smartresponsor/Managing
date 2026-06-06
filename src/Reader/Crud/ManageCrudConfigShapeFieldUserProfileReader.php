<?php

declare(strict_types=1);

namespace App\Managing\Reader\Crud;

use App\Managing\ReaderInterface\Crud\ManageCrudFieldUserProfileReaderInterface;
use App\Managing\Value\Crud\ManageCrudFieldUserProfileReadResult;
use App\Managing\Value\Crud\ManageCrudFieldUserProfileRuleSet;

/**
 * Config-shaped reader for hosts that still source personal view profiles from configuration.
 */
final readonly class ManageCrudConfigShapeFieldUserProfileReader implements ManageCrudFieldUserProfileReaderInterface
{
    /** @var array<string, mixed> */
    private array $profileConfig;

    /** @param array<string, mixed> $fieldUserProfilesConfig */
    public function __construct(array $fieldUserProfilesConfig = [])
    {
        $this->profileConfig = ManageCrudFieldUserProfileRuleSet::fromArray($fieldUserProfilesConfig)->toArray();
    }

    public function read(?string $subjectIdentifier = null): ManageCrudFieldUserProfileReadResult
    {
        $subjectIdentifier = $this->nullableString($subjectIdentifier);
        if (null === $subjectIdentifier) {
            return ManageCrudFieldUserProfileReadResult::available($this->profileConfig, 'field_user_profile_config_read_available');
        }

        $subjects = $this->profileConfig['subjects'] ?? [];
        if (!is_array($subjects) || !isset($subjects[$subjectIdentifier])) {
            return ManageCrudFieldUserProfileReadResult::available(['subjects' => []], 'field_user_profile_config_subject_missing');
        }

        return ManageCrudFieldUserProfileReadResult::available([
            'subjects' => [
                $subjectIdentifier => $subjects[$subjectIdentifier],
            ],
        ], 'field_user_profile_config_subject_available');
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
