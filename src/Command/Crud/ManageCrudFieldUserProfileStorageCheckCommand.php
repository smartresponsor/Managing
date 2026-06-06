<?php

declare(strict_types=1);

namespace App\Managing\Command\Crud;

use App\Managing\ValidatorInterface\Crud\ManageCrudFieldUserProfileStorageActivationValidatorInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'managing:field-view-profile-storage:check',
    description: 'Checks explicit Managing field view profile storage activation.',
)]
final class ManageCrudFieldUserProfileStorageCheckCommand extends Command
{
    public function __construct(
        private readonly ManageCrudFieldUserProfileStorageActivationValidatorInterface $validator,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $report = $this->validator->validate();

        $io->title('Managing field view profile storage check');
        $io->writeln(sprintf('Doctrine storage enabled: %s', $report->doctrineStorageEnabled ? 'yes' : 'no'));
        $io->writeln(sprintf('Status: %s', $report->statusLabel()));

        foreach ($report->issues as $issue) {
            $io->writeln(sprintf('[%s] %s: %s', $issue->severity, $issue->code, $issue->message));
        }

        if (!$report->passed) {
            $io->error('Managing field view profile storage activation is not safe yet.');

            return Command::FAILURE;
        }

        $io->success('Managing field view profile storage activation check passed.');

        return Command::SUCCESS;
    }
}
