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
* That you're committing using the ssh key that is located at ~/agent.signing
* That you're committing using the name and email located in ~/agent_info.txt

# Pull Requests

When creating a pull request, follow these guidelines:

* It should include a clear title and description of the changes made.
* It should reference the JIRA ticket if applicable.
* It should be assigned to the appropriate reviewer (that-guy-iain).
* It should be linked to the relevant JIRA ticket.
* If there is already a pull request for the same branch, you should not create a new one. Instead, update the existing pull request with your changes.
* There should only be one pull request per JIRA ticket.