.PHONY: all
all: vendor

vendor: composer.json
	composer install

install: composer.json
	composer install

test: phpunit phpcs composer-validate composer-outdated

phpunit: vendor
	composer run-script phpunit:coverage

debug: vendor
	composer run-script phpunit:debug

phpcs: vendor
	composer run-script phpcs

composer-validate: vendor
	composer validate --no-check-publish

composer-outdated: vendor
	composer outdated

.PHONY: clean
clean:
	rm -rf coverage/ vendor/ .phpunit.cache .php-cs-fixer.cache
