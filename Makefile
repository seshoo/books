##################
# Variables
##################

DOCKER_COMPOSE = docker-compose -f ./docker/docker-compose.yml --env-file ./docker/.env
DOCKER_COMPOSE_PHP_FPM_EXEC = ${DOCKER_COMPOSE} exec -u www-data php-fpm

##################
# Common
##################

qs:
	# копируем env файл для докера
	cp ./docker/.env.dist ./docker/.env

	# создаем папку для хранения данных базы
	mkdir -p ./docker/postgres/db_data

	# поднимает контейнеры
	make dc_up

	# устанавливаем зависимости
	make composer_install

	# выполняем миграции
	make db_migrate

	# загрузка тестовых данных
	make db_load_fixtures

##################
# Docker compose
##################

dc_build:
	${DOCKER_COMPOSE} build --pull --no-cache

dc_start:
	${DOCKER_COMPOSE} start

dc_stop:
	${DOCKER_COMPOSE} stop

dc_up:
	${DOCKER_COMPOSE} up -d --remove-orphans

dc_ps:
	${DOCKER_COMPOSE} ps

dc_logs:
	${DOCKER_COMPOSE} logs -f

dc_down:
	${DOCKER_COMPOSE} down -v --rmi=all --remove-orphans

dc_restart:
	make dc_stop dc_start

##################
# App
##################

php:
	${DOCKER_COMPOSE} exec -u www-data php-fpm fish

composer_install:
	${DOCKER_COMPOSE} exec -u www-data php-fpm composer i

jwt_generate_keypair:
	${DOCKER_COMPOSE} exec -u www-data php-fpm php ./bin/console lexik:jwt:generate-keypair

##################
# Database
##################

db_migrate:
	${DOCKER_COMPOSE} exec -u www-data php-fpm php ./bin/console doctrine:migrations:migrate --no-interaction

db_load_fixtures:
	${DOCKER_COMPOSE} exec -u www-data php-fpm php ./bin/console doctrine:fixtures:load

db_drop:
	${DOCKER_COMPOSE} exec -u www-data php-fpm php ./bin/console doctrine:schema:drop --force

db_create:
	${DOCKER_COMPOSE} exec -u www-data php-fpm php ./bin/console doctrine:schema:create

db_recreate:
	make db_drop
	make db_create

db_test_create:
	${DOCKER_COMPOSE} exec -u www-data php-fpm php bin/console doctrine:database:create --env=test
