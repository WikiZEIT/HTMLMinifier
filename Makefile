PHP=$(shell command -v php83 >/dev/null 2>&1 && echo php83 || echo php)
VERSION=0.1.0

.PHONY: test coverage publish version

all: vendor

vendor:
	composer install

test:
	$(PHP) vendor/bin/phpunit --display-deprecations

coverage:
	XDEBUG_MODE=coverage $(PHP) vendor/bin/phpunit --coverage-text --coverage-clover coverage.xml --display-deprecations

version:
	sed -i 's/"version": *"[^"]*"/"version": "$(VERSION)"/' composer.json
	sed -i 's/packagist-[0-9.]*-/packagist-$(VERSION)-/' README.md
	composer install

publish: version test
	git tag -a "$(VERSION)" -m "Release $(VERSION)"
	git push origin "$(VERSION)"
