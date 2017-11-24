-include $(CURDIR)/.env

_:
	make init

init:
	docker-compose up -d
	make composer
	yarn
	make import

composer:
	bash tools/composer/composer.sh

gen-movefile:
	bash tools/wordmove/generate.sh 0

gen-movefile-with-libraries:
	bash tools/wordmove/generate.sh 1

clean:
	docker-compose down
	docker-compose up -d
	make import

# Production pull
prd-pull-all:
	bash tools/wordmove/move.sh production pull --all

prd-pull-db:
	bash tools/wordmove/move.sh production pull -d

prd-pull-core:
	bash tools/wordmove/move.sh production pull -w

prd-pull-themes:
	bash tools/wordmove/move.sh production pull -t

prd-pull-plugins:
	bash tools/wordmove/move.sh production pull -p

prd-pull-uploads:
	bash tools/wordmove/move.sh production pull -u

# Production push
prd-push-all:
	bash tools/wordmove/move.sh production push --all

prd-push-db:
	bash tools/wordmove/move.sh production push -d

prd-push-core:
	bash tools/wordmove/move.sh production push -w

prd-push-themes:
	bash tools/wordmove/move.sh production push -t

prd-push-plugins:
	bash tools/wordmove/move.sh production push -p

prd-push-uploads:
	bash tools/wordmove/move.sh production push -u

# Staging pull
stg-pull-all:
	bash tools/wordmove/move.sh staging pull --all

stg-pull-db:
	bash tools/wordmove/move.sh staging pull -d

stg-pull-core:
	bash tools/wordmove/move.sh staging pull -w

stg-pull-themes:
	bash tools/wordmove/move.sh staging pull -t

stg-pull-plugins:
	bash tools/wordmove/move.sh staging pull -p

stg-pull-uploads:
	bash tools/wordmove/move.sh staging pull -u

# Staging push
stg-push-all:
	bash tools/wordmove/move.sh staging push --all

stg-push-db:
	bash tools/wordmove/move.sh staging push -d

stg-push-core:
	bash tools/wordmove/move.sh staging push -w

stg-push-themes:
	bash tools/wordmove/move.sh staging push -t

stg-push-plugins:
	bash tools/wordmove/move.sh staging push -p

stg-push-uploads:
	bash tools/wordmove/move.sh staging push -u

# DUMP
import:
	bash tools/shell-scripts/db-import.sh

import-from-production:
	bash tools/shell-scripts/db-import.sh $(PRODUCTION_DOMAIN) $(LOCAL_DOMAIN)

import-from-staging:
	bash tools/shell-scripts/db-import.sh $(STAGING_DOMAIN) $(LOCAL_DOMAIN)

export:
	bash tools/shell-scripts/db-export.sh

export-to-production:
	bash tools/shell-scripts/db-export.sh $(PRODUCTION_DOMAIN) $(LOCAL_DOMAIN)

export-to-staging:
	bash tools/shell-scripts/db-export.sh $(STAGING_DOMAIN) $(LOCAL_DOMAIN)
