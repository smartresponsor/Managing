<?php

declare(strict_types=1);

namespace App\Managing\Service\Form;

use App\Managing\ServiceInterface\Admin\ManageAdminRegistryInterface;
use App\Managing\ServiceInterface\Admin\ManageContributionFilterInterface;
use App\Managing\ServiceInterface\Form\ManageFormRegistryInterface;

final readonly class ManageFormRegistry implements ManageFormRegistryInterface
{
    public function __construct(
        private ManageAdminRegistryInterface $adminRegistry,
        private ManageContributionFilterInterface $contributionFilter,
    ) {
    }

    public function getForms(): array
    {
        $forms = [];

        foreach ($this->adminRegistry->getProviders() as $provider) {
            foreach ($provider->getForms() as $form) {
                if ($this->contributionFilter->isFormEnabled($form)) {
                    $forms[] = $form;
                }
            }
        }

        return $forms;
    }
}
