<?php

declare(strict_types=1);

namespace Rugaard\GitHooks\Workflow\Hooks\CommitMsg;

use Rugaard\GitHooks\Abstracts\AbstractCommand;
use Rugaard\GitHooks\Style\GitHookStyle;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class IssueReferenceCommand.
 *
 * @package Rugaard\GitHooks\Workflow\Hooks\CommitMsg
 */
class IssueReferenceCommand extends AbstractCommand
{
    /**
     * Configures the current command.
     *
     * @return void
     */
    public function configure(): void
    {
        $this->setName('github:issue')
             ->setDescription('Check that commit message contains an issue reference.')
             ->addArgument('message', InputArgument::REQUIRED, 'Commit log message');
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
        // Get path to commit message file.
        $pathToCommitMessageFile = $this->getGitTopLevelDirectory() . '/' . $input->getArgument('message');

        // Get commit message.
        $commitMessage = rtrim(file_get_contents($pathToCommitMessageFile), "\n");

        // Instantiate Git Hooks Styles.
        $io = new GitHookStyle($input, $output);

        // Generate section.
        $io->header('Issue reference', false);

        // Output that we're looking for issue reference in commit message.
        $io->writeln('<fg=cyan>[INFO] Checking commit message ...</>');

        // Check commit message for issue reference.
        if (preg_match('/' . $this->getConfig('commitPattern') . '/i', $commitMessage, $issueNo) > 0) {
            $io->block('Found ' . $this->getConfig('forcePrefix') . $issueNo[1] . '.', 'OK', 'fg=green;options=bold', '');
            return Command::SUCCESS;
        }

        // Output that no issue reference was found in commit message,
        // and we're therefore looking for one in the branch name.
        $io->writeln('<fg=yellow>[NOTE] Issue reference not found in commit message.</>' . "\n");
        $io->writeln('<fg=cyan>[INFO] Checking branch name ...</>');

        // Get current branch of repository.
        $currentBranch = $this->getGitBranchCurrent();

        // Check branch name for issue reference.
        if (preg_match('/' . $this->getConfig('branchPattern') . '/i', $currentBranch, $issueNo) > 0) {
            $this->addIssueReferenceToCommitMessageFile($issueNo[1], $pathToCommitMessageFile);
            $io->block('Found ' . $this->getConfig('forcePrefix') . $issueNo[1] . '. Added to commit message.', 'OK', 'fg=green;options=bold', '');
            return Command::SUCCESS;
        }

        // Output that no issue reference was found in branch name.
        $io->writeln('<fg=yellow>[NOTE] Issue reference not found in branch name.</>' . "\n");

        // Abort commit, since no issue reference was provided.
        $io->block('Aborting commit. No issue reference was provided.', 'ERROR', 'fg=red;options=bold', '');

        return Command::FAILURE;
    }

    /**
     * Add issue reference to commit message file.
     *
     * @param string $issueReference
     * @param string $pathToCommitMessageFile
     *
     * @return bool
     */
    protected function addIssueReferenceToCommitMessageFile(string $issueReference, string $pathToCommitMessageFile): bool
    {
        // Get current commit message.
        $currentCommitMessage = (string) file_get_contents($pathToCommitMessageFile);

        // Make sure issue reference starts with prefix.
        if (!empty($this->getConfig('forcePrefix')) && substr($issueReference, 0, strlen($this->getConfig('forcePrefix'))) !== $this->getConfig('forcePrefix')) {
            $issueReference = $this->getConfig('forcePrefix') . $issueReference;
        }

        // Prepend issue reference to commit message.
        return (bool) file_put_contents($pathToCommitMessageFile, $issueReference . ' ' . $currentCommitMessage);
    }

    /**
     * Get command's default configuration.
     *
     * @return array
     */
    protected function getDefaultConfig(): array
    {
        return [
            'branchPattern' => '.*\/#?([0-9]+)-',
            'commitPattern' => '#([0-9]+)',
            'forcePrefix' => '#',
        ];
    }

    /**
     * Type of git-hook command belongs to.
     *
     * @return string
     */
    public static function hookType(): string
    {
        return 'commit-msg';
    }
}
