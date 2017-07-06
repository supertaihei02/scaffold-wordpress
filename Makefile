_:
	make init

init:
	cp .env.sample .env
	mkdir -p data/mysql
	docker-compose up -d
	yarn
	make import

clean:
	docker-compose down
	rm -rf data
	mkdir -p data/mysql
	docker-compose up -d
	make import

import:
	bash tools/shell-scripts/db-import.sh

export:
	bash tools/shell-scripts/db-export.sh
