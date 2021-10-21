<?php

declare(strict_types=1);

namespace Rugaard\GitHooks\Workflow\Hooks\PreCommit;

use Rugaard\GitHooks\Abstracts\AbstractCommand;
use Rugaard\GitHooks\Style\GitHookStyle;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class BranchNameCommand.
 *
 * @package Rugaard\GitHooks\Workflow\Hooks\PreCommit
 */
class BranchNameCommand extends AbstractCommand
{
    /**
     * Configures the current command.
     *
     * @return void
     */
    public function configure(): void
    {
        $this->setName('github:branch')
             ->setDescription('Checks that current branch name, follows the projects naming convention.');
    }

    /**
     * Executes the current command.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        // Instantiate Git Hooks Styles.
        $io = new GitHookStyle($input, $output);

        // Generate section.
        $io->header('Branch name', false);

        // Get current branch name.
        $branchName = rtrim($this->getGitBranchCurrent(), "\n");

        if (in_array($branchName, $this->getConfig('whitelist')) || preg_match('/' . $this->getConfig('pattern') . '/i', $branchName) > 0) {
            $io->block('Done.', 'OK', 'fg=green;options=bold', '');
            return Command::SUCCESS;
        }

        $io->block('Invalid branch name. Rename branch and try again.', 'ERROR', 'fg=red;options=bold', '');
        $io->writeln('<fg=yellow>How to rename branch:</>' . "\n  " . 'git branch -m "' . $this->getConfig('renameExample') . '"');

        return Command::FAILURE;
    }

    /**
     * Get command's default configuration.
     *
     * @return array
     */
    protected function getDefaultConfig(): array
    {
        return [
            'pattern' => '^(bugfix|feature|hotfix|release)\/(\d+)-([A-Za-z0-9-]+)$',
            'renameExample' => '{type}/{issue-no}-{description}',
            'whitelist' => ['main', 'master', 'develop'],
        ];
    }

    /**
     * Type of git-hook command belongs to.
     *
     * @return string
     */
    public static function hookType(): string
    {
        return 'pre-commit';
    }
}
