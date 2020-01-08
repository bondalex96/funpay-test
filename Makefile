.DEFAULT_GOAL := help
help:
	@grep -E '^[a-zA-Z-]+:.*?## .*$$' Makefile | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "[32m%-17s[0m %s\n", $$1, $$2}'
.PHONY: help

initialize: ## Initialize project
	docker-compose up -d
	docker-compose run --rm php composer install

check-code: ## Check code
	docker-compose run --rm php vendor/bin/phpunit
