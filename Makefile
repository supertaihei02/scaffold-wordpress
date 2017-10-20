-include $(CURDIR)/.env

_:
	make init

init:
	cp .env.sample .env
	docker-compose up -d
	yarn
	make import

clean:
	docker-compose down
	docker-compose up -d
	make import

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
