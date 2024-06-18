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

## PSH COMMANDS
## Use PSH Hoster Module
psh-branch:
	git merge -X theirs origin/feature/A-000-PSH --allow-unrelated-histories --no-edit

## Run this command to get access to the rabbitmq ui. After running open http://localhost:15672/ in your browser. (https://docs.platform.sh/add-services/rabbitmq.html)
rabbit-ui:
	ssh -L 15672:rabbitmq.internal:15672 $$(platform ssh --pipe)

template-sync-shopware:
	git pull upstream/main

template-sync-psh:
	git pull upstream/PSH

