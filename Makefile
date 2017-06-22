init:
	cp .env.sample .env
	mkdir -p data/mysql
	docker-compose up -d
	yarn

clean:
	docker-compose down
	rm -rf data
	mkdir -p data/mysql
	docker-compose up -d
