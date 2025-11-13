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

##########
# Docker #
##########

docker-base:
	docker compose exec shopware bash -c "${COMMAND}"

## Install the project (this must only be done once)
install:
	# printf "${COLOR_CYAN}Check if shopware-cli is installed${COLOR_RESET}\n"
	# shopware-cli --version || (printf "${COLOR_CYAN}Execute \"brew install FriendsOfShopware/tap/shopware-cli\" first${COLOR_RESET}\n" && exit 1)

	# printf "${COLOR_CYAN}Check if port 30001 is used${COLOR_RESET}\n"
	# lsof -i -P -n | grep LISTEN | grep 127.0.0.1:30001 || (printf "${COLOR_CYAN}Execute \"platform tunnel:single -p nu23l7no2x7te -r database --port 30001 -e develop\" first${COLOR_RESET}\n" && exit 1)

	printf "${COLOR_CYAN}Shutting down shopware${COLOR_RESET}\n"
	docker compose down --remove-orphans || true

	printf "${COLOR_CYAN}Removing src${COLOR_RESET}\n"
	rm -rf src

	printf "${COLOR_CYAN}Starting temporary shopware instance${COLOR_RESET}\n"
	$(eval CONTAINER_ID=$(shell sh -c 'docker run -d dockware/dev:6.6.10.5'))

	printf "${COLOR_CYAN}Copying source code into src${COLOR_RESET}\n"
	docker cp "${CONTAINER_ID}:/var/www/html/." ./src

	# printf "${COLOR_CYAN}Replacing .env${COLOR_RESET}\n"
	# mv src/.env src/.env.bak # keep the original .env as reference
	# cp .env.dockware src/.env

	printf "${COLOR_CYAN}Shutting down temporary container${COLOR_RESET}\n"
	docker kill "${CONTAINER_ID}" && docker rm "${CONTAINER_ID}"

	printf "${COLOR_CYAN}Starting container${COLOR_RESET}\n"
	docker compose up -d --build --force-recreate

	printf "${COLOR_CYAN}Fixing permissions${COLOR_RESET}\n"
	make docker-base COMMAND="sudo chown -R www-data:www-data /var/www/html/custom/static-plugins/TemplateTheme"

	printf "${COLOR_CYAN}Remove dummy plugins${COLOR_RESET}\n"
	make docker-base COMMAND="rm -rf /var/www/html/custom/plugins/DockwareSamplePlugin /var/www/html/custom/plugins/SwagPlatformDemoData || true"

	sleep 15 # wait until the container is up and running

	printf "${COLOR_CYAN}Run composer install${COLOR_RESET}\n"
	make docker-base COMMAND="composer install --no-scripts"

	printf "${COLOR_CYAN}Install basic setup${COLOR_RESET}\n"
	make docker-base COMMAND="bin/console system:install --force --drop-database --basic-setup --no-debug"

	printf "${COLOR_CYAN}Installing and activating plugins${COLOR_RESET}\n"
	make docker-base COMMAND="APP_DEBUG=0 make prepare"

	printf "${COLOR_CYAN}Copy vendor to host${COLOR_RESET}\n"
	docker cp $$(docker compose ps -q shopware):/var/www/html/vendor ./src

	# printf "${COLOR_CYAN}Load staging database${COLOR_RESET}\n"
	# make sync-staging-database

	# printf "${COLOR_CYAN}Load staging media${COLOR_RESET}\n"
	# make sync-staging-media

	printf "${COLOR_CYAN}Run Elasticsearch index${COLOR_RESET}\n"
	make docker-base COMMAND="bin/console es:index --no-queue --no-debug"

	printf "${COLOR_CYAN}Change to default theme${COLOR_RESET}\n"
	make docker-base COMMAND="bin/console theme:change --all Storefront --no-compile && bin/console theme:dump && bin/console theme:compile --sync"

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
	bin/console plugin:refresh
	bin/console plugin:install -n --activate BasecomFixturePlugin
	bin/console plugin:install -n --activate CustomFieldsPlugin
	bin/console plugin:install -n --activate FroshTools
	bin/console plugin:install -n --activate TemplateTheme

## Run the linting tools for all customer-specific plugins from the main project
lint:
	./scripts/lint.sh

## Run the testing tools for all customer-specific plugins from the main project
test:
	./scripts/test.sh

## Run the testing tools, including coverage, for all customer-specific plugins from the main project
test-coverage:
	./scripts/test-coverage.sh

## shortcut to delete test database
delete-test-database:
	docker compose exec shopware bash -c "mysql -uroot -proot -e 'DROP DATABASE shopware_test;'"

## PSH COMMANDS
## Use PSH Hoster Module
## Run this command to get access to the rabbitmq ui. After running open http://localhost:15672/ in your browser. (https://docs.platform.sh/add-services/rabbitmq.html)
rabbit-ui:
	ssh -L 15672:rabbitmq.internal:15672 $$(platform ssh --pipe)

template-sync-shopware:
	git pull upstream/main

template-sync-psh:
	git pull upstream/PSH

