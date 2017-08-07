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

export:
	bash tools/shell-scripts/db-export.sh
