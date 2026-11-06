.DEFAULT_GOAL := help
.PHONY: *

help:
	@printf "\033[33mUsage:\033[0m\n  make [target] [arg=\"val\"...]\n\n\033[33mTargets:\033[0m\n"
	@grep -E '^[-a-zA-Z0-9_\.\/]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[32m%-30s\033[0m %s\n", $$1, $$2}'

clean: ## Remove development tools and composer created files
	rm -rf vendor/ composer.lock
	perl -i -pe 's/ installed=".*?"//' .phive/phars.xml
	@printf "To reinstall dependencies run: make update\n"

update-composer: ## Update project direct dependencies
	php8.5 /usr/local/bin/composer update

update-phive: ## Update development tools dependencies
	php8.5 /usr/local/bin/phive install phpcs phpcbf php-cs-fixer phpstan

update: update-composer update-phive ## Update all dependencies

style-check-php-cs-fixer: ## Check code style using php-cs-fixer
	php8.5 tools/php-cs-fixer fix --dry-run --verbose

style-check-phpcs: ## Check code style using phpcs
	php8.5 tools/phpcs --colors -sp

style-check: style-check-php-cs-fixer style-check-phpcs ## Check code style using php-cs-fixer and phpcs

style-fix-php-cs-fixer: ## Fix code style using php-cs-fixer
	php8.5 tools/php-cs-fixer fix --verbose

style-fix-phpcbf: ## Fix code style using phpcbf
	php8.5 tools/phpcbf --colors -sp || true

style-fix: style-fix-php-cs-fixer style-fix-phpcbf ## Fix code style using php-cs-fixer and phpcbf

phpunit: ## Run code tests
	php8.5 vendor/bin/phpunit

phpstan: ## Run code analysis
	php8.5 tools/phpstan analyze

build: style-fix style-check phpstan phpunit ## Run style fix, style check, phpstan and phpunit

