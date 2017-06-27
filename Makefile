init:
	cp .env.sample .env
	mkdir -p data/mysql
	docker-compose up -d
	yarn
	sh tools/shell-scripts/import.sh

clean:
	docker-compose down
	rm -rf data
	mkdir -p data/mysql
	docker-compose up -d
	sh tools/shell-scripts/import.sh
