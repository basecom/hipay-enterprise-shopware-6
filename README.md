# Shopware 6 PSH Template

## How to setup your project

Hey there, you successfully added PSH hoster files to your project. Follow these steps to finish 
project initialization.

1. Push project to your repository with `git push origin main`
2. Remove `setup.sh`
3. Set the Papple and FriendsOfShopware token in `auth.json`.
4. ..
5. Add the [Platform.sh GitLab integration](https://docs.platform.sh/integrations/source/gitlab.html)
6. Rename the title `Shopware 6 PSH Template` to something meaningful and delete this section

## Local development setup
1. Clone the repository via gitlab
2. Start the containers using docker: `docker compose up -d`
3. Run `make shell` to enter the shell and then `make prepare` to install all dependencies und load all fixtures

## Local linting / testing
You may run the linting- and testing-suites from the main project for all customer-specific plugins, like the theme.

1. Enter the shell: `make shell`
2. In the shell you may now run `make lint` for linting or `make test` for phpunit
3. Coverage reports can be generated with `make test-coverage`
4. The coverage reports will be located in `./plugins/<YourPlugin>/coverage`

---

### Fetch template updates
The template is designed for the projects to be cloned as a fork from the original template (remote _upstream_). This enables you to pull the newest changes from the template into your repository.  
- Use `make template-sync-shopware` to sync the templates shopware code with your repository
- Use `make template-sync-psh` to sync the templates Platform.sh code with your repository

### Configure Shopware
You have two options to configure the shopware installation. You can edit/create new package configurations in the `config/packages` directory and you can change the environment variables used in the `.platform.app.yaml` file.

#### Edit environment variables
A few variables are set in the `.platform.app.yaml` file. You can modify all variables set in `.environment` to your needs. The default should work for all projects. Please edit with care!

#### See template theme readme
Please also look at the README.md file of the included template theme for more instructions.
