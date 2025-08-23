Git Usage
=========

This document provides a quick reference for common Git commands and workflows.

# Branching and Merging

This flow must exist

Basic Flow for a feature is as follows:

* Pull the latest changes from the main branch with `git pull --rebase origin main`.
* Create a new branch from main for your feature with the name of the branch based on the feature you are working on.
* Make changes to the code.
* Stage the changes with `git add .` or `git add <specific files>`.
* Commit the changes with a descriptive message using `git commit -m "Your message here"
* Push the changes to the remote repository with `git push origin <branch-name>` using the ssh key located at `~/agent`.
* Create a pull request (PR) to merge your changes into the main branch if one doesn't exist

# Git Commit Requirements

When committing changes, ensure that:

* If there are PHP changes run `vendor/bin/php-cs-fixer fix --allow-unsupported-php-version=yes` to ensure the code style is correct
* The commit message MUST start with a prefix for the JIRA ticket if there is one if not DH-NIL
* The commit message is clear and descriptive.
* That you're committing using the gpg key that is located at ~/agent.signing
* That you're committing using the name and email located in ~/agent_info.txt

# Pull Requests

You only create pull requests for new tasks. When handling code review for existing pull requests, you do not create new pull requests. Instead, you update the existing pull request by pushing additional commits to the same branch that the pull request was created from.

## Updating Existing Pull Requests

* YOU MUST PULL THE LATEST CHANGES FROM THE FEATURE BRANCH WITH `git pull --rebase origin feature/DH-3-a-feature` BEFORE PUSHING ANY NEW COMMITS TO AN EXISTING PULL REQUEST.
* Make any necessary changes or additions to the code.
* Stage the changes with `git add .` or `git add <specific files>`.
* Commit the changes with a descriptive message using `git commit -m "Your message here"
* Push the changes to the remote repository with `git push origin <branch-name>` using the ssh key located at `~/agent`.
* The existing pull request will automatically update with the new commits

## Create Pull Requests

When creating a pull request, follow these guidelines:

* It should include a clear title and description of the changes made.
* It should reference the JIRA ticket if applicable.
* It should be assigned to the appropriate reviewer (that-guy-iain).
* It should be linked to the relevant JIRA ticket.
