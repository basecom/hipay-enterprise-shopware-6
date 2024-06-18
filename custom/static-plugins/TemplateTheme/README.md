# Template plugin
This is a template plugin and should be used for every new Shopware 6 plugin.

## Features
- Includes Shopware 6 boilerplate code
- Makefile
- Configuration for various linting and testing tools
- Ready-to-use gitlab pipeline configuration
- Can be used as standalone plugin
- Can be used as a customer-specific plugin
- Dockware docker image for full-featured development enviroment with a Shopware 6 instance containing a database, webserver and XDebug

| Type                   | Tool                      |
|------------------------|---------------------------|
| Linting                | Prettier<br/>PHP-CS-Fixer |
| Static Code Analysis   | Psalm<br/> PHPStan        |
| Unit/Integration Tests | PHPUnit                   |


## How to use

> If you want to use it as a customer-specific plugin inside the customer project, download the template as .zip file, extract it and place it in the `plugins` directory in the customer project. Only execute the steps 1 and 2. Never clone it as a git repository!

1. Clone/download this template
2. Change the plugin name in the following files:
    - `Makefile`
    - `composer.json`
    - `package.json`
    - `src/Resources/config/services.yaml`
    - `src/BasecomTemplatePlugin.php`
    - `gitlab-ci.yml`
4. Create a new GitLab repository
5. Initialize the repository and do your first commit


### Start developing
This template uses a full-featured Dockware docker image. It already comes with a pre-installed Shopware 6 instance and everything you need to start developing.

Please see the [Dockware documentation](https://dockware.io/docs).

To start developing, simply start the container:
```bash
> docker compose up -d
```

Access the container:
```bash
> make shell
```

Install the dependencies and make everything ready (defined in composer.json and package.json). This command needs to be
executed from the host-system (not in shell)
```bash
> make install
```

### Linting
Before committing, please run the linting and static analysis tools. This command also needs to be executed from the
host machine (not in shell):
```bash
> make lint
```

### Testing
This template is using *PHPUnit* for unit and integration testing. Run the following command before committing your changes.
This command needs to be executed on the host system (not in shell).

Run tests and generate coverage report:
```bash
> make test
```

#### Testsuites
There are two types of tests: unit tests and integration tests. Put all unit tests in `tests/Unit` and all integrations tests in `tests/Integration`. Make sure that the testsuites are configured in the `phpunit.xml.dist` file.

#### Coverage
Every piece of code should be tested. Nevertheless, the coverage threshold can be adjusted in the `.gitlab-ci.yml`

### GitLab pipeline
The GitLab pipeline is already pre-configured. It contains multiple jobs for all linting, static analysis and testing tools.

The pipeline runs all tests for the latest stable Shopware 6 version. But you have several options for the PHPUnit tests:

#### PHPUnit
You can configure a build matrix for the `phpunit` job.
Just add the PHP and the Shopware 6 versions you want to test to the `matrix` list in `.gitlab.yml`.

For example: You want to test your plugin with PHP `7.4` and `8.0`. And you want to test it with Shopware `6.4.5.1`, `6.4.6.1` and `6.4.7.0`.

The matrix will then look like this:
```yaml
matrix:
  - PHP_VERSION: [ "7.4", "8.0" ]
    SW_VERSION: [ "6.4.5.1", "6.4.6.1", "6.4.7.0" ]
```

With this configuration, GitLab runs 6 parallel tests: each Shopware version combined with each PHP version.

> Please always test at least with the latest PHP version and the latest Shopware version!

## Use as theme
If you want to use this template as a theme, just make the following changes:
1. Add the interface `ThemeInterface` to the plugin base class and add a `getThemeConfigPath()` method:
   ```php
   class TemplateTheme extends Plugin implements ThemeInterface
   {
       public function getThemeConfigPath(): string
       {
           return 'theme.json';
       }
   }
   ```

2. Create `theme.json` in the `src/Resources` directory with the following content:
   ```json
   {
     "name": "TemplateTheme",
     "author": "basecom GmbH & Co. KG",
     "views": [
       "@Storefront",
       "@Plugins",
       "@TemplateTheme"
     ],
     "style": [
       "@Storefront",
       "app/storefront/src/scss/base.scss"
     ],
     "script": [
       "@Storefront",
       "app/storefront/dist/storefront/js/template-theme.js"
     ],
     "asset": [
       "app/storefront/src/assets"
     ],
     "config": {
       "fields": {}
     }
   }
   ```
3. The theme should always be named `<Project>Theme`. Replace `<Project>` with the project name.

## Common problems

### Plugin is not installed during phpunit test suite
If shopware throws an exception during test execution, stating that a plugin is not installed, you need
to modify the `tests/TestBootstrap.php` file.

Uncomment the following line and reexecute the tests:
```php
//->setForceInstallPlugins(true)
```

After that you should comment in the line again and further tests should
still be working!
