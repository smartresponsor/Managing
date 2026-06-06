<?php

declare(strict_types=1);

namespace App\Managing\Writer\Crud;

use App\Managing\RepositoryInterface\Crud\ManageCrudFieldViewProfileRuleRepositoryInterface;
use App\Managing\Value\Crud\ManageCrudFieldUserProfileWriteRequest;
use App\Managing\Value\Crud\ManageCrudFieldUserProfileWriteResult;
use App\Managing\WriterInterface\Crud\ManageCrudFieldUserProfileWriterInterface;

/**
 * Doctrine/system-storage-backed writer for Managing personal field view profiles.
 */
final readonly class ManageCrudDoctrineFieldUserProfileWriter implements ManageCrudFieldUserProfileWriterInterface
{
    public function __construct(private ManageCrudFieldViewProfileRuleRepositoryInterface $repository)
    {
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

        return ManageCrudFieldUserProfileWriteResult::accepted(
            $this->repository->replacePageRule($request),
            'field_user_profile_doctrine_write_applied',
        );
    }
}
