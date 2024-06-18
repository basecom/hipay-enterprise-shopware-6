# Shopware 6 Template

## How to setup your project

1. Create a remote repository for your project (_without readme initialization_)
2. Clone this project to your local machine
3. Execute `./setup.sh`
   1. Choose your hoster
   2. Provide the target repository url (Code &rarr; Clone with SSH)
4. Push project to your repository with `git push origin main`

## How to contribute to this template

### Structure

This repository includes the main branch (shopware-only-code) and hoster-specific-branches (shopware-code + hoster-specific code).
When a project is initially set up, a hoster branch will be merged into the main branch, enabling a quick project initialization.
Because hoster and shopware code are split, maintenance of them will differ.

### Updating hoster files

Updating hoster files (like psh, maxcluster etc.) is easy. Create a branch from the hoster branch, apply your changes and merge it back to the hoster branch.

### Updating shopware files

Please update the shopware files with a branch from the main branch and merge it back. Once merged, the changes made in the main branch need to be propagated to the 
hoster branches, since they also include shopware files. To do so feel free to use the `make hoster-update` command. Please make sure to solve upcoming merge-conflicts correctly.
