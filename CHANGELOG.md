# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

**Order for categories:**
- Security
- Added
- Changed
- Fixed
- Removed

## [4.0.0]
### Changed
- **BREAKING!** Shopware version update to newest minor (6.5)
- **BREAKING!** Restructured project structure to be closer to the standard used in [`Shopware PaaS`](https://developer.shopware.com/docs/products/paas/setup-template.html)
- Change PHP setting [`serialize_precision`](https://www.php.net/manual/en/ini.core.php#ini.serialize-precision) to `-1`
    in the platform.sh config to prevent rounding errors in the JSON output (e.g. of the Store API). The default setting
    in the PSH environment is `17`, the default setting by PHP is `-1`. See also the
    [#bsc-devops-shopware](https://grow-digital-group.slack.com/archives/C049BTBQMK4/p1701349243297679) channel on Slack.

## [3.6.0] - 2023-11-20
### Changed
- Add RabbitMQ Service with normal and high priority queue
- Change Elasticsearch to Opensearch

## [3.5.1]
### Changed
- Add monolog.yaml and .env config to mountpoints

## [3.5.0] - 2023-09-05
### Changed
- Database changed to MySQL due to [critical bug in MariaDB Version 10.5.22](https://jira.basecom.de/browse/TSD-6249)

## [3.4.0]
### Changed
- The primary .gitlab-ci.yml now uses
  [child pipelines ](https://docs.gitlab.com/ee/ci/pipelines/downstream_pipelines.html#parent-child-pipelines)
  for running the plugin CI jobs. This prevents problems when multiple plugins exist in a project.
- The Pipelines use the Hetzner Runners by default (cheaper and better performance).
- The Pipeline Jobs are interruptible by default. When a new Pipeline is started all running Jobs for the
  same branch are cancelled.

## [3.3.0] - 2023-04-03
### Added
- new command for updating all upgradable plugins
- add command to deploy step

## [3.2.0] - 2023-03-15
### Added
- Capability to autoload from `tests/` directory while testing

### Changed
- Use `sprintf()` for exception messages in `CustomTestBootstrapper`

### Fixed
- Local coverage task now works (it was missing `-dpcov.enabled=1 -dpcov.directory="${d}/."` in the scripts)

## [3.1.0] - 2023-02-23
### Added
- Added webroot feature
- Added security.txt

## [3.0.0] - 2023-01-23
### Added
- Hard delete all cache directories during platform.sh deployments to prevent the storage from filling up with
  cache folders of previous releases.

### Changed
- **BREAKING!** The `/config/jwt` mount is changed from local to shared storage to prevent issues with message queue
  task execution. Some tasks need access to the JWT certificates to function properly. For migrating exising projects
  additionally make these adjustments in `.platform.app.yaml`:

  In the mounts section add:

  ```yaml
  mounts:
      "/config-old/jwt":
          source: local
          source_path: "config/jwt"
  ```

  In the deploy section add **before** the databse migrations:

  ```bash
  if [ -d config-old/jwt ] && [ "$(ls -A config-old/jwt)" ]; then
      mv config-old/jwt/* config/jwt
  fi
  ```

  After the deployment (and the jwt files were moved to the new storage) both entries can be removed again.
- Re-use the script for running message queue workers to run the scheduled task runner. With this change the scheduler
  also gets improved cache clearing during  releases, verbose output and log cleanup.

## [2.1.0] - 2022-11-04
### Changed
- Updated Shopware to 6.4.16.1
- Disabled xdebug by default

### Fixed
- Customer Fixture for BasecomFixturePlugin Update

## [2.0.0]
### Changed
- **BREAKING!** The local mounts are replaced with network storage mounts. This makes sure the message bus workers have
  access to the same files as the web container. This fixes problems with data export and thumbnail generation.

  This change requires manual adjustments after deployment! Move files in `/files` and `/public` mounts from the
  old local storages in the web container to the new shared storages:

  ```shell
  mv files-old/* files/
  mv public-old/media/* public/media/
  mv public-old/thumbnail/* public/thumbnail/
  # If you have custom public folders in your project, move those as well!
  ```

  The remaining folders in the `/public` mount can be re-initalized and do not need to be moved:

  ```shell
  ./bin/console assets:install
  ./bin/console theme:compile
  ./bin/console sitemap:generate
  ```
- The storage size of the remaining local mounts is reduced to 512MB (from 1GB). This should be enough because the
  majority of the large data is stored in the network storage. It is required to prevent exceeding resource limits
  in smaller test / dev environments.
- Add the shell script `scripts/psh/run_messenger_consume.sh` and execute it in the workers instead of directly
  executing the `message:consume` command. The script handles cache clearing for new releases and aggressive
  log cleanup because the worker nodes only have 256 MB of storage.
- Increase verbosity in PSH build hook by replacing comments with echo.

### Fixed
- Define dummy REDIS env vars in the PSH build hook to prevent Exceptions.
- Fix typo in `$APP_ENV` condition in PSH build hook.
- Configure mount for `/public/recovery` to prevent errors during PSH build.
- Better order for theme generation/build commands to ensure asset:install does not override compiled theme

## [1.2.1] - 2022-10-18
### Added
- Added standard symfony logger for error logs in prod

### Changed
- Switched to Packagist/Github version of FixturesPlugin and update to version 2.0

## [1.2.0] - 2022-08-15
### Added
- Added support to install composer dependencies, requiring php 8.0 or above

### Fixed
- Fixed sitemap generation (mounted folder was missing)

### Removed
- Removed rabbitMQ (for now), because Shopware default queue implementation requires the delay module, which isn't supported in Platform.sh

  **Important!** The message queue stats should be cleaned up to prevent inconsistencies. One symptom of this
  is the message "Generating thumbnails..." in the Administration being displayed all the time. Execute this SQL
  query to fix it:

  ```sql
  DELETE FROM increment WHERE pool='message_queue';
  ```

- Removed unused "swiftmailer-bundle" from dependencies.json

## [1.1.0] - 2022-05-20
### Added
- Added FixturesPlugin and default fixture for custom account registration
- Added example custom fields file, since the custom field plugin fails without one
- Added example theme with configured pipeline
- Added scripts to execute linting / testing for all customer-specific plugins without starting their own docker containers:
  - `make test`
  - `make test-coverage`
  - `make lint`
- Added custom test bootstrapper, to install required plugins in the correct order

### Changed
- Execute fixtures in group `production` on deployment
- Updated to PHP 8.1
- Updated redis to version v6.2
- Updated rabbitmq to version v3.9
- Split redis cache into sessions and general cache, so that clearing the general cache won't sign out users
- Update some dependencies (NVM, Node)
- Optimize admin building on platform.sh deployment
- Optimize PHP settings for production
- Updated shopware to v6.4.11.1
- Delete / Update custom fields on deployment

### Fixed
- Fixed issue, where fixtures something won't be copied on platform.sh deployment
- Fixed mailer configuration for platform.sh

### Removed
- Removed dependency of `SwagPaas`, since it is now build-in
- Removed custom docker entrypoint, because it breaks newer dockware versions and is already included


## 1.0.0 - 2022-03-21
### Added
- Initial release
- Added all necessary files for platform.sh and dockware

[3.6.0]: https://gitlab.com/basecom-gmbh/shopware/v6/customer-projects/templates/sw6-platformsh-template/-/compare/3.5.1...3.6.0
[3.5.1]: https://gitlab.com/basecom-gmbh/shopware/v6/customer-projects/templates/sw6-platformsh-template/-/compare/3.5.0...3.5.1
[3.5.0]: https://gitlab.com/basecom-gmbh/shopware/v6/customer-projects/templates/sw6-platformsh-template/-/compare/3.4.0...3.5.0
[3.4.0]: https://gitlab.com/basecom-gmbh/shopware/v6/customer-projects/templates/sw6-platformsh-template/-/compare/3.3.0...3.4.0
[3.3.0]: https://gitlab.com/basecom-gmbh/shopware/v6/customer-projects/templates/sw6-platformsh-template/-/compare/3.2.0...3.3.0
[3.2.0]: https://gitlab.com/basecom-gmbh/shopware/v6/customer-projects/templates/sw6-platformsh-template/-/compare/3.1.0...3.2.0
[3.1.0]: https://gitlab.com/basecom-gmbh/shopware/v6/customer-projects/templates/sw6-platformsh-template/-/compare/3.0.0...3.1.0
[3.0.0]: https://gitlab.com/basecom-gmbh/shopware/v6/customer-projects/templates/sw6-platformsh-template/-/compare/2.1.0...3.0.0
[2.1.0]: https://gitlab.com/basecom-gmbh/shopware/v6/customer-projects/templates/sw6-platformsh-template/-/compare/2.0.0...2.1.0
[2.0.0]: https://gitlab.com/basecom-gmbh/shopware/v6/customer-projects/templates/sw6-platformsh-template/-/compare/1.2.1...2.0.0
[1.2.1]: https://gitlab.com/basecom-gmbh/shopware/v6/customer-projects/templates/sw6-platformsh-template/-/compare/1.2.0...1.2.1
[1.2.0]: https://gitlab.com/basecom-gmbh/shopware/v6/customer-projects/templates/sw6-platformsh-template/-/compare/1.1.0...1.2.0
[1.1.0]: https://gitlab.com/basecom-gmbh/shopware/v6/customer-projects/templates/sw6-platformsh-template/-/compare/1.0.0...1.1.0
