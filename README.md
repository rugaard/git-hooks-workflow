# 🪝 Git Hooks for workflow [![GitHub Actions (tests)](https://github.com/rugaard/git-hooks-workflow/actions/workflows/tests.yml/badge.svg?branch=main)](https://github.com/rugaard/git-hooks-workflow/actions/workflows/tests.yml)

This is a "plugin" package which seamlessly integrates with the [Git Hooks](https://github.com/rugaard/git-hooks) package. 

It will install `git` hooks, that will automatically run multiple checks on your project to make sure, they follow the projects workflow and naming conventions.

## 📦 Installation

You install the package via [Composer](https://getcomposer.org/) by using the following command:

```shell
composer require rugaard/git-hooks-workflow
```

## 📝 Configuration

To change the default configuration of one or more script, you need to have a `git-hooks.config.json` file in your project root. If you don't, you can create it with the following command:

```shell
./vendor/bin/git-hooks config
```

### `Rugaard\GitHooks\Workflow\CommitMsg\IssueReferenceCommand`

Checks the commit for an issue reference. Default is GitHub format.

If commit message does not contain an issue reference, the branch name will be checked to see, if an issue reference can be extracted.

| Parameter | Description | Default |
| :--- | :--- | :---: |
| `branchPattern` | Regex pattern to search branch name for issue reference. | `.*\/#?([0-9]+)-` |
| `commitPattern` | Regex pattern to search commit message for issue reference. | `#([0-9]+)` |
| `forcePrefix` | Make sure issue reference is always prefixed with this value. | `#` |

### `Rugaard\GitHooks\Workflow\PreCommit\BranchNameCommand`

Checks the current branch name and make sure it follows the projects naming convention.

| Parameter | Description | Default |
| :--- | :--- | :---: |
| `pattern` | Regex pattern to validate branch name | `^(bugfix\|feature\|hotfix\|release)\/(\d+)-([A-Za-z0-9-]+)$` |
| `renameExample` | Format of branch naming convention | `{type}/{issue-no}-{description}` |
| `whitelist` | Array of branch names that skips this check. | `['main', 'master', 'develop']` |

## 🚓 License

This package is licensed under [MIT](https://github.com/rugaard/git-hooks-github/blob/main/LICENSE).
