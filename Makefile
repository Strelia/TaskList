OS := $(shell uname)

start_dev:  
ifeq ($(OS),Darwin)
	docker-sync start
	docker volume create --name=app-sync
	docker-compose -f docker-compose.yml up -d
	docker exec -it gfl-php chown -R www-data:www-data var/ || :
	docker exec -it gfl-php bash
else
	docker-compose up -d
	docker exec -it gfl-php chown -R www-data:www-data var/
	docker exec -it gfl-php bash
endif

stop_dev:
ifeq ($(OS),Darwin)
	docker-compose stop
	docker-sync stop
#	docker-sync clean
else
	docker-compose stop
endif
