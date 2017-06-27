init:
	cp .env.sample .env
	mkdir -p data/mysql
	docker-compose up -d
	yarn
	sh operation/db/import.sh

clean:
	docker-compose down
	rm -rf data
	mkdir -p data/mysql
	docker-compose up -d
	sh operation/db/import.sh
