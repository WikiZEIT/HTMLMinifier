PHP=$(shell command -v php83 >/dev/null 2>&1 && echo php83 || echo php)

.PHONY: test coverage

all: vendor

vendor:
	composer install

test:
	$(PHP) vendor/bin/phpunit --display-deprecations

coverage:
	XDEBUG_MODE=coverage $(PHP) vendor/bin/phpunit --coverage-text --display-deprecations
