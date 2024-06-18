.SILENT:
.PHONY: build

## Colors
COLOR_RESET   = \033[0m
COLOR_INFO    = \033[32m
COLOR_COMMENT = \033[33m

## Show Help
help:
	printf "${COLOR_COMMENT}Usage:${COLOR_RESET}\n"
	printf " make [target]\n\n"
	printf "${COLOR_COMMENT}Available targets:${COLOR_RESET}\n"
	awk '/^[a-zA-Z-]+:/ { \
		helpMessage = match(lastLine, /^## (.*)/); \
		if (helpMessage) { \
			helpCommand = substr($$1, 0, index($$1, ":")); \
			helpMessage = substr(lastLine, RSTART + 3, RLENGTH); \
			printf " ${COLOR_INFO}%-16s${COLOR_RESET} %s\n", helpCommand, helpMessage; \
		} \
	} \
	{ lastLine = $$0 }' $(MAKEFILE_LIST)


## Connect to ssh (password: dockware)
ssh:
	ssh -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null dockware@localhost

## Connect to the bash container
shell:
	docker-compose exec shopware bash

update-plugins:
	./scripts/update-plugins.sh

###############
# Platform.sh #
###############
permissions:
	sudo chmod -R 777 * && sudo chmod 600 config/jwt/*.pem

####################
# Project specific #
####################

## Prepares the whole application, including activating all necessary plugins and executing the fixtures.
prepare:
	composer install --no-scripts
	bin/console plugin:refresh
	bin/console plugin:install -n --activate BasecomFixturePlugin
	bin/console plugin:install -n --activate CustomFieldsPlugin TemplateTheme
	bin/console theme:dump
	bin/build-storefront.sh
	bin/console theme:change --all TemplateTheme
	bin/console fixture:load

## Run the linting tools for all customer-specific plugins from the main project
lint:
	./scripts/lint.sh

## Run the testing tools for all customer-specific plugins from the main project
test:
	./scripts/test.sh

## Run the testing tools, including coverage, for all customer-specific plugins from the main project
test-coverage:
	./scripts/test-coverage.sh

# Some merge command that merges the changes from main into all hoster branches, already excluding files that
# will definitely cause merge conflicts and where its obvious which one to keep (like Makefile [keep hoster branch])
hoster-update:
	echo "ToDo"
