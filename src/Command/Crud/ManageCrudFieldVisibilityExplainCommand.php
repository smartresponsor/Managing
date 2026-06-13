<?php

declare(strict_types=1);

namespace App\Managing\Command\Crud;

use App\Managing\InspectorInterface\Crud\ManageCrudFieldVisibilityInspectorInterface;
use App\Managing\Value\Crud\ManageCrudFieldVisibilityInspectionReport;
use App\Managing\Value\Crud\ManageCrudFieldVisibilityInspectionRequest;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'managing:field-visibility:explain',
    description: 'Explains why a Managing CRUD field is visible, hidden, or denied.',
)]
final class ManageCrudFieldVisibilityExplainCommand extends Command
{
    public function __construct(
        private readonly ManageCrudFieldVisibilityInspectorInterface $inspector,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('resource', InputArgument::REQUIRED, 'Resource/entity FQCN to inspect.')
            ->addArgument('field', InputArgument::REQUIRED, 'Field nameEntity to inspect.')
            ->addArgument('page', InputArgument::REQUIRED, 'EasyAdmin page: index, detail, new, or edit.')
            ->addOption('subject', null, InputOption::VALUE_REQUIRED, 'Optional subject identifier, for example user:42.')
            ->addOption('status-field', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Status field candidate. Repeatable.')
            ->addOption('publication-flag-field', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Publication flag field candidate. Repeatable.')
            ->addOption('publication-date-field', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Publication date field candidate. Repeatable.')
            ->addOption('json', null, InputOption::VALUE_NONE, 'Print machine-readable JSON.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $report = $this->inspector->inspect(new ManageCrudFieldVisibilityInspectionRequest(
            resourceClass: (string) $input->getArgument('resource'),
            fieldName: (string) $input->getArgument('field'),
            pageName: (string) $input->getArgument('page'),
            subjectIdentifier: $this->nullableString($input->getOption('subject')),
            statusCandidates: $this->stringList($input->getOption('status-field')),
            publicationFlagCandidates: $this->stringList($input->getOption('publication-flag-field')),
            publicationDateCandidates: $this->stringList($input->getOption('publication-date-field')),
        ));

        if ((bool) $input->getOption('json')) {
            $output->writeln((string) json_encode($report->toArray(), JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

            return $report->found() ? Command::SUCCESS : Command::FAILURE;
        }

        return $this->renderHumanReport(new SymfonyStyle($input, $output), $report);
    }

    private function renderHumanReport(SymfonyStyle $io, ManageCrudFieldVisibilityInspectionReport $report): int
    {
        $io->title('Managing field visibility explanation');
        $io->writeln(sprintf('Resource: %s', $report->request->resourceClass));
        $io->writeln(sprintf('Field: %s', $report->request->fieldName));
        $io->writeln(sprintf('Page: %s', $report->request->pageName));
        $io->writeln(sprintf('Subject: %s', $report->request->subjectIdentifier ?? 'none'));
        $io->writeln(sprintf('Status: %s', $report->statusLabel()));
        $io->writeln(sprintf('Decision axis: %s', $report->decisionAxis()));

        if (null === $report->explanation) {
            $io->error($report->reason ?? 'field_visibility_explanation_not_available');

            return Command::FAILURE;
        }

        $io->section('Final decision');
        $io->writeln(sprintf('Renderable: %s', $report->renderable() ? 'yes' : 'no'));
        $io->writeln(sprintf('Access allowed: %s', $report->explanation->finalDecision->accessAllowed ? 'yes' : 'no'));
        $io->writeln(sprintf('Visible: %s', $report->explanation->finalDecision->visible ? 'yes' : 'no'));
        $io->writeln(sprintf('Source: %s', $report->explanation->finalDecision->source));
        $io->writeln(sprintf('Reason: %s', $report->explanation->finalDecision->reason ?? 'none'));
        $io->writeln(sprintf('Access denied: %s', $report->explanation->accessDenied() ? 'yes' : 'no'));
        $io->writeln(sprintf('Presentation hidden: %s', $report->explanation->presentationHidden() ? 'yes' : 'no'));

        $io->section('Trace');
        foreach ($report->explanation->steps as $index => $step) {
            $io->writeln(sprintf(
                '%d. [%s/%s] %s: %s%s',
                $index + 1,
                $step->source,
                $step->axis,
                $step->effect,
                $step->reason,
                $step->terminal ? ' (terminal)' : '',
            ));
        }

        return Command::SUCCESS;
    }

    /** @return list<string> */
    private function stringList(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        return array_values(array_filter(array_map('strval', $value), static fn (string $item): bool => '' !== $item));
    }

    private function nullableString(mixed $value): ?string
    {
        if (null === $value || '' === $value) {
            return null;
        }

        return (string) $value;
    }
}
